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

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    respond(401, ['ok' => false, 'error' => 'Not logged in']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

$userId = (int)$_SESSION['user_id'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];

// Extract and validate input
$courseId = (int)($data['courseId'] ?? 0);
$rating = (float)($data['rating'] ?? 0);
$comment = trim((string)($data['comment'] ?? ''));

if ($courseId === 0) {
    respond(400, ['ok' => false, 'error' => 'Invalid courseId']);
}

if ($rating < 1 || $rating > 5) {
    respond(400, ['ok' => false, 'error' => 'Rating must be between 1 and 5']);
}

if (empty($comment)) {
    respond(400, ['ok' => false, 'error' => 'Comment is required']);
}

try {
    // Check if course exists
    $courseStmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? LIMIT 1");
    $courseStmt->execute([$courseId]);
    if (!$courseStmt->fetch()) {
        respond(404, ['ok' => false, 'error' => 'Course not found']);
    }

    // Check if user is enrolled
    $enrollStmt = $pdo->prepare("SELECT id FROM enrollments WHERE userid = ? AND courseid = ? LIMIT 1");
    $enrollStmt->execute([$userId, $courseId]);
    if (!$enrollStmt->fetch()) {
        respond(403, ['ok' => false, 'error' => 'You must be enrolled to review this course']);
    }

    // Check if user already reviewed this course
    $existingStmt = $pdo->prepare("SELECT id FROM coursereviews WHERE userid = ? AND courseid = ? LIMIT 1");
    $existingStmt->execute([$userId, $courseId]);
    $existing = $existingStmt->fetch();

    if ($existing) {
        // Update existing review
        $updateStmt = $pdo->prepare("
            UPDATE coursereviews 
            SET rating = ?, comment = ?, createdat = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$rating, $comment, (int)$existing['id']]);
        $reviewId = (int)$existing['id'];
        $message = 'Review updated successfully';
    } else {
        // Insert new review
        $insertStmt = $pdo->prepare("
            INSERT INTO coursereviews (userid, courseid, rating, comment, createdat) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertStmt->execute([$userId, $courseId, $rating, $comment]);
        $reviewId = (int)$pdo->lastInsertId();
        $message = 'Review submitted successfully';
    }

    respond(200, [
        'ok' => true,
        'reviewId' => $reviewId,
        'message' => $message
    ]);

} catch (Throwable $e) {
    respond(500, ['ok' => false, 'error' => 'Server error']);
}
