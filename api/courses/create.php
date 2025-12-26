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

$instructorId = (int)$_SESSION['user_id'];
$role = (string)($_SESSION['role'] ?? 'learner');

if ($role !== 'teacher' && $role !== 'admin') {
    respond(403, ['ok' => false, 'error' => 'Only teachers can create courses']);
}

// Get form data
$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$difficulty = trim($_POST['difficulty'] ?? 'Beginner');
$totalLessons = (int)($_POST['lessons'] ?? 0);
$durationHours = isset($_POST['duration']) ? (float)$_POST['duration'] : null;
$isFree = (string)($_POST['isfree'] ?? '0') === '1';
$price = (float)($_POST['price'] ?? 0);

if ($isFree) {
    $price = 0.0;
}

// Validation
if (!$title || !$category || !$description) {
    respond(400, ['ok' => false, 'error' => 'Title, category and description are required']);
}

if ($totalLessons <= 0) {
    respond(400, ['ok' => false, 'error' => 'Total lessons must be at least 1']);
}

if (!in_array($difficulty, ['Beginner', 'Intermediate', 'Advanced'], true)) {
    $difficulty = 'Beginner';
}

// Handle thumbnail upload
$thumbnailUrl = '';
if (isset($_FILES['thumbnail']) && is_array($_FILES['thumbnail']) && 
    ($_FILES['thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    
    if (($_FILES['thumbnail']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        respond(400, ['ok' => false, 'error' => 'Thumbnail upload failed']);
    }

    $tmp = (string)$_FILES['thumbnail']['tmp_name'];
    $origName = (string)$_FILES['thumbnail']['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed, true)) {
        respond(400, ['ok' => false, 'error' => 'Thumbnail must be JPG/PNG/WEBP']);
    }

    // Create upload directory
    $uploadDir = __DIR__ . '/../../uploads/course_thumbs';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $safeName = 'course_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $uploadDir . '/' . $safeName;

    if (!move_uploaded_file($tmp, $dest)) {
        respond(500, ['ok' => false, 'error' => 'Failed to save thumbnail']);
    }

    $thumbnailUrl = '/proj/uploads/course_thumbs/' . $safeName;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO courses 
        (instructor_id, title, category, description, difficulty, total_lessons, 
         duration_hours, thumbnail_url, price, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $instructorId,
        $title,
        $category,
        $description,
        $difficulty,
        $totalLessons,
        $durationHours,
        $thumbnailUrl ?: null,
        $price
    ]);

    $courseId = (int)$pdo->lastInsertId();

    respond(201, [
        'ok' => true,
        'courseId' => $courseId,
        'message' => 'Course created successfully'
    ]);

} catch (Throwable $e) {
    error_log('Course create error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

