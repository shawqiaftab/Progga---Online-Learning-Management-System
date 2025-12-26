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

$userId = (int)$_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'learner';

try {
    if ($userRole === 'teacher' || $userRole === 'admin') {
        // Get courses created by teacher
        $stmt = $pdo->prepare("
            SELECT 
                id,
                title,
                category,
                description,
                thumbnail_url,
                difficulty,
                total_lessons,
                duration_hours,
                price,
                is_free,
                created_at
            FROM courses
            WHERE teacher_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
    } else {
        // Get courses enrolled by student
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.title,
                c.category,
                c.description,
                c.thumbnail_url,
                c.difficulty,
                c.total_lessons,
                c.duration_hours,
                c.price,
                c.is_free,
                e.enrolled_at,
                COALESCE(cp.progress_percent, 0) AS progress_percent
            FROM enrollments e
            INNER JOIN courses c ON c.id = e.course_id
            LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
            WHERE e.user_id = ?
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$userId]);
    }

    $courses = $stmt->fetchAll();

    respond(200, [
        'ok' => true,
        'courses' => $courses,
        'count' => count($courses)
    ]);

} catch (Throwable $e) {
    error_log("Courses mine error: " . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
