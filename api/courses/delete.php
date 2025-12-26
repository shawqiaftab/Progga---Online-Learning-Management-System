<?php
declare(strict_types=1);

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

session_start();
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// DEBUG: Log the request method
error_log("DELETE API CALLED - Method: " . $_SERVER['REQUEST_METHOD']);

// Check session
if (empty($_SESSION['user_id'])) {
    error_log("DELETE API - Not logged in");
    respond(401, ['ok' => false, 'error' => 'Not logged in']);
}

$userId  = (int)$_SESSION['user_id'];
$userRole = (string)($_SESSION['role'] ?? 'learner');

error_log("DELETE API - User: $userId, Role: $userRole, Method: " . $_SERVER['REQUEST_METHOD']);

// Get courseId - ACCEPT ALL METHODS
$courseId = 0;
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    error_log("DELETE API - POST Body: " . $rawBody);
    $body = json_decode($rawBody, true) ?? [];
    $courseId = (int)($body['courseId'] ?? $body['id'] ?? 0);
    error_log("DELETE API - POST courseId: $courseId");
} elseif ($method === 'DELETE') {
    $courseId = (int)($_GET['id'] ?? $_GET['courseId'] ?? 0);
    error_log("DELETE API - DELETE courseId: $courseId");
} elseif ($method === 'GET') {
    // Some JS frameworks send GET for debugging
    $courseId = (int)($_GET['id'] ?? $_GET['courseId'] ?? 0);
    error_log("DELETE API - GET courseId: $courseId");
} else {
    error_log("DELETE API - UNSUPPORTED METHOD: $method");
    respond(405, ['ok' => false, 'error' => "Method $method not allowed. Use POST or DELETE"]);
}

if ($courseId <= 0) {
    error_log("DELETE API - Invalid courseId: $courseId");
    respond(400, ['ok' => false, 'error' => 'Invalid course id']);
}

try {
    // Check ownership
    $stmt = $pdo->prepare("SELECT instructor_id, title FROM courses WHERE id = ? LIMIT 1");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        error_log("DELETE API - Course $courseId not found");
        respond(404, ['ok' => false, 'error' => 'Course not found']);
    }

    error_log("DELETE API - Course found: " . $course['title'] . ", Instructor: " . $course['instructor_id']);

    if ($userRole !== 'admin' && (int)$course['instructor_id'] !== $userId) {
        error_log("DELETE API - Permission denied. Course instructor: " . $course['instructor_id'] . ", User: $userId");
        respond(403, ['ok' => false, 'error' => 'Not allowed']);
    }

    error_log("DELETE API - Starting deletion of course $courseId");

    $pdo->beginTransaction();

    // Delete related data
    $pdo->exec("DELETE FROM exam_attempt_answers WHERE attempt_id IN (
        SELECT ea.id FROM exam_attempts ea
        JOIN exams e ON e.id = ea.exam_id
        WHERE e.course_id = $courseId
    )");
    error_log("DELETE API - Deleted exam_attempt_answers");

    $pdo->exec("DELETE FROM exam_attempts WHERE exam_id IN (
        SELECT id FROM exams WHERE course_id = $courseId
    )");
    error_log("DELETE API - Deleted exam_attempts");

    $pdo->exec("DELETE FROM exam_choices WHERE question_id IN (
        SELECT id FROM exam_questions WHERE exam_id IN (
            SELECT id FROM exams WHERE course_id = $courseId
        )
    )");
    error_log("DELETE API - Deleted exam_choices");

    $pdo->exec("DELETE FROM exam_questions WHERE exam_id IN (
        SELECT id FROM exams WHERE course_id = $courseId
    )");
    error_log("DELETE API - Deleted exam_questions");

    $pdo->prepare("DELETE FROM exams WHERE course_id = ?")->execute([$courseId]);
    error_log("DELETE API - Deleted exams");

    $pdo->prepare("DELETE FROM coursereviews WHERE courseid = ?")->execute([$courseId]);
    error_log("DELETE API - Deleted coursereviews");

    $pdo->prepare("DELETE FROM course_reviews WHERE course_id = ?")->execute([$courseId]);
    error_log("DELETE API - Deleted course_reviews");

    $pdo->prepare("DELETE FROM course_progress WHERE course_id = ?")->execute([$courseId]);
    $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?")->execute([$courseId]);
    $pdo->prepare("DELETE FROM course_purchases WHERE course_id = ?")->execute([$courseId]);
    $pdo->prepare("DELETE FROM course_discounts WHERE course_id = ?")->execute([$courseId]);
    $pdo->prepare("DELETE FROM teacher_messages WHERE course_id = ?")->execute([$courseId]);

    $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$courseId]);
    error_log("DELETE API - Deleted course");

    $pdo->commit();
    error_log("DELETE API - SUCCESS");

    respond(200, [
        'ok' => true,
        'message' => 'Course deleted successfully',
        'courseId' => $courseId,
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("DELETE API - ERROR: " . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

