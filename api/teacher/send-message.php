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

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data)) json_error("Invalid JSON", 400);

$courseId  = (int)($data['course_id'] ?? 0);
$studentId = (int)($data['student_id'] ?? 0);
$msg       = trim((string)($data['message'] ?? ''));

if ($courseId <= 0 || $studentId <= 0 || $msg === '') json_error("Missing data", 400);

$chk = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE id = ? AND instructor_id = ?");
$chk->execute([$courseId, $teacherId]);
if ((int)$chk->fetchColumn() !== 1) json_error("Not allowed", 403);


$chk2 = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND user_id = ?");
$chk2->execute([$courseId, $studentId]);
if ((int)$chk2->fetchColumn() !== 1) json_error("Student not enrolled", 409);


$ins = $pdo->prepare("
  INSERT INTO teacher_messages (teacher_id, student_id, course_id, message, created_at)
  VALUES (?, ?, ?, ?, NOW())
");
$ins->execute([$teacherId, $studentId, $courseId, $msg]);

json_ok(["ok" => true, "message" => "Sent"]);
