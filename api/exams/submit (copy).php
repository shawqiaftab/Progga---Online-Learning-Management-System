<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

if (empty($_SESSION['user_id'])) {
    respond(401, ['ok' => false, 'error' => 'Not logged in']);
}

$userId = (int)$_SESSION['user_id'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$attemptId = (int)($body['attemptId'] ?? 0);

if ($attemptId <= 0) {
    respond(400, ['ok' => false, 'error' => 'Invalid attemptId']);
}

try {
    // Get attempt details
    $aStmt = $pdo->prepare("SELECT exam_id, submitted_at FROM exam_attempts WHERE id = ? AND user_id = ? LIMIT 1");
    $aStmt->execute([$attemptId, $userId]);
    $attempt = $aStmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        respond(403, ['ok' => false, 'error' => 'Attempt not found']);
    }

    if (!empty($attempt['submitted_at'])) {
        respond(200, ['ok' => true, 'attemptId' => $attemptId, 'message' => 'Already submitted']);
    }

    $examId = (int)$attempt['exam_id'];

    // Count total questions
    $tStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM exam_questions WHERE exam_id = ?");
    $tStmt->execute([$examId]);
    $total = (int)($tStmt->fetch()['cnt'] ?? 0);

    if ($total === 0) {
        respond(400, ['ok' => false, 'error' => 'Exam has no questions']);
    }

    // Count correct answers
    $correctStmt = $pdo->prepare("
        SELECT COUNT(*) AS correct_cnt
        FROM exam_attempt_answers aa
        INNER JOIN exam_choices ch ON ch.id = aa.choice_id
        INNER JOIN exam_questions q ON q.id = aa.question_id
        WHERE aa.attempt_id = ? AND q.exam_id = ? AND ch.is_correct = 1
    ");
    $correctStmt->execute([$attemptId, $examId]);
    $correct = (int)($correctStmt->fetch()['correct_cnt'] ?? 0);

    $scorePercent = round(($correct / $total) * 100, 2);
    $incorrect = $total - $correct;

    // Get passing score
    $passStmt = $pdo->prepare("SELECT passing_score FROM exams WHERE id = ? LIMIT 1");
    $passStmt->execute([$examId]);
    $passing = (float)($passStmt->fetch()['passing_score'] ?? 50);
    $passed = $scorePercent >= $passing ? 1 : 0;

    // Update attempt with results
    $now = (new DateTime())->format('Y-m-d H:i:s');
    $upd = $pdo->prepare("
        UPDATE exam_attempts 
        SET submitted_at = ?, 
            score_percent = ?, 
            correct = ?,
            incorrect = ?,
            total = ?,
            passed = ?
        WHERE id = ? AND user_id = ?
    ");
    $upd->execute([$now, $scorePercent, $correct, $incorrect, $total, $passed, $attemptId, $userId]);

    respond(200, [
        'ok' => true,
        'attemptId' => $attemptId,
        'scorePercent' => $scorePercent,
        'correct' => $correct,
        'incorrect' => $incorrect,
        'total' => $total,
        'passed' => (bool)$passed
    ]);

} catch (Throwable $e) {
    error_log('Exam submit error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error']);
}

