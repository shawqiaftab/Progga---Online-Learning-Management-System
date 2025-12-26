<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    respond(401, ['ok' => false, 'error' => 'Not logged in. Please login to submit a review.']);
}

$userId = (int)$_SESSION['user_id'];

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    respond(400, ['ok' => false, 'error' => 'Invalid JSON input']);
}

$courseId = (int)($data['courseId'] ?? 0);
$rating = (float)($data['rating'] ?? 0);
$comment = trim((string)($data['comment'] ?? ''));

// Validate inputs
if ($courseId <= 0) {
    respond(400, ['ok' => false, 'error' => 'Invalid course ID']);
}

if ($rating < 1 || $rating > 5) {
    respond(400, ['ok' => false, 'error' => 'Rating must be between 1 and 5']);
}

if (empty($comment)) {
    respond(400, ['ok' => false, 'error' => 'Please write a comment']);
}

if (strlen($comment) > 500) {
    respond(400, ['ok' => false, 'error' => 'Comment is too long (max 500 characters)']);
}

try {
    // Check if course exists
    $courseStmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = ? LIMIT 1");
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        respond(404, ['ok' => false, 'error' => 'Course not found']);
    }

    // Check if user is enrolled (students only need enrollment, teachers can review their own courses)
    $userRole = $_SESSION['role'] ?? 'learner';

    if ($userRole !== 'teacher' && $userRole !== 'admin') {
        $enrollStmt = $pdo->prepare("
            SELECT id FROM enrollments 
            WHERE user_id = ? AND course_id = ? 
            LIMIT 1
        ");
        $enrollStmt->execute([$userId, $courseId]);

        if (!$enrollStmt->fetch()) {
            respond(403, ['ok' => false, 'error' => 'You must be enrolled in this course to leave a review']);
        }
    }

    // Check if user has already reviewed this course
    $existingStmt = $pdo->prepare("
        SELECT id FROM coursereviews 
        WHERE userid = ? AND courseid = ? 
        LIMIT 1
    ");
    $existingStmt->execute([$userId, $courseId]);
    $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing review
        $updateStmt = $pdo->prepare("
            UPDATE coursereviews 
            SET rating = ?, comment = ?, createdat = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$rating, $comment, $existing['id']]);

        respond(200, [
            'ok' => true,
            'message' => 'Review updated successfully!',
            'updated' => true
        ]);
    } else {
        // Insert new review
        $insertStmt = $pdo->prepare("
            INSERT INTO coursereviews (userid, courseid, rating, comment, createdat)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertStmt->execute([$userId, $courseId, $rating, $comment]);

        respond(201, [
            'ok' => true,
            'message' => 'Review submitted successfully!',
            'reviewId' => (int)$pdo->lastInsertId()
        ]);
    }

} catch (PDOException $e) {
    error_log("Review submit DB error: " . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    error_log("Review submit error: " . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
