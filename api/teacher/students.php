<?php
declare(strict_types=1);

session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
  http_response_code(401);
  header('Content-Type: application/json; charset=utf-8'); // [web:470]
  echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
  exit;
}

require __DIR__ . '/../_lib/json.php';
require __DIR__ . '/../../config/db.php';

$teacherId = (int)$_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($courseId <= 0) json_error('course_id is required', 400);


$chk = $pdo->prepare("SELECT id, title, total_lessons FROM courses WHERE id = ? AND instructor_id = ? LIMIT 1");
$chk->execute([$courseId, $teacherId]);
$course = $chk->fetch(PDO::FETCH_ASSOC);
if (!$course) json_error('Not allowed', 403);

$stmt = $pdo->prepare("
  SELECT
    u.id AS student_id,
    u.name,
    u.email,
    COALESCE(p.progress_percent, 0) AS progress_percent,
    COALESCE(p.lessons_done, 0) AS lessons_done
  FROM enrollments e
  JOIN users u ON u.id = e.user_id
  LEFT JOIN course_progress p
    ON p.user_id = e.user_id AND p.course_id = e.course_id
  WHERE e.course_id = ?
  ORDER BY u.name
");
$stmt->execute([$courseId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

json_ok([
  'ok' => true,
  'course' => [
    'id' => (int)$course['id'],
    'title' => (string)$course['title'],
    'total_lessons' => (int)($course['total_lessons'] ?? 0),
  ],
  'students' => array_map(static function($s) {
    return [
      'student_id' => (int)$s['student_id'],
      'name' => (string)$s['name'],
      'email' => (string)$s['email'],
      'progress_percent' => (float)$s['progress_percent'],
      'lessons_done' => (int)$s['lessons_done'],
    ];
  }, $students),
]);
