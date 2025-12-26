<?php
declare(strict_types=1);
require __DIR__ . '/../auth/guard.php'; // or your existing auth guard
require __DIR__ . '/../_lib/json.php';
require __DIR__ . '/../../config/db.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$role   = (string)($_SESSION['role'] ?? '');

if ($userId === 0) {
    json_error('Unauthorized', 401);
}
if (!in_array($role, ['teacher', 'admin'], true)) {
    json_error('Forbidden', 403);
}

$courseId = isset($_GET['courseId']) ? (int)$_GET['courseId'] : (int)($_GET['courseid'] ?? 0);
if ($courseId <= 0) {
    json_error('courseId is required', 400);
}

try {
    // Check teacher owns the course (or is admin)
    $cStmt = $pdo->prepare("SELECT id, title, instructor_id FROM courses WHERE id = ? LIMIT 1");
    $cStmt->execute([$courseId]);
    $course = $cStmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        json_error('Course not found', 404);
    }

    if ($role !== 'admin' && (int)$course['instructor_id'] !== $userId) {
        json_error('Forbidden', 403);
    }

    // Summary
    $sStmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS totalreviews,
            AVG(overall_rating) AS overallavg,
            AVG(content_rating) AS contentavg,
            AVG(clarity_rating) AS clarityavg
        FROM course_reviews
        WHERE course_id = ?
    ");
    $sStmt->execute([$courseId]);
    $s = $sStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Reviews list
    $rStmt = $pdo->prepare("
        SELECT 
            r.id,
            r.overall_rating,
            r.comment,
            r.created_at,
            u.name AS reviewername
        FROM course_reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.course_id = ?
        ORDER BY r.created_at DESC
        LIMIT 200
    ");
    $rStmt->execute([$courseId]);

    $reviews = [];
    while ($row = $rStmt->fetch(PDO::FETCH_ASSOC)) {
        $createdRaw = $row['created_at'] ?? '';
        $human = $createdRaw ? date('j M, Y', strtotime($createdRaw)) : '';

        $reviews[] = [
            'id'            => (int)$row['id'],
            'reviewerName'  => (string)($row['reviewername'] ?? 'Student'),
            'createdAtHuman'=> $human,
            'overallRating' => (float)($row['overall_rating'] ?? 0),
            'comment'       => (string)($row['comment'] ?? ''),
        ];
    }

    json_ok([
        'ok'         => true,
        'courseTitle'=> (string)$course['title'],
        'summary'    => [
            'totalReviews' => (int)($s['totalreviews'] ?? 0),
            'overallAvg'   => (float)($s['overallavg'] ?? 0),
            'contentAvg'   => (float)($s['contentavg'] ?? 0),
            'clarityAvg'   => (float)($s['clarityavg'] ?? 0),
        ],
        'reviews'    => $reviews,
    ]);

} catch (Throwable $e) {
    error_log('reviews/by-course error: ' . $e->getMessage());
    json_error('Server error', 500);
}

