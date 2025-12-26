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
  respond(405, ["ok" => false, "error" => "Method not allowed"]);
}

if (empty($_SESSION['user_id'])) {
  respond(401, ["ok" => false, "error" => "Not logged in"]);
}

$userId = (int)$_SESSION['user_id'];

// Get course ID from request
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$courseId = (int)($data['course_id'] ?? $_POST['course_id'] ?? 0);

if ($courseId <= 0) {
  respond(400, ["ok" => false, "error" => "course_id required"]);
}

try {
  // Check if course exists
  $courseStmt = $pdo->prepare("SELECT id, price, instructor_id FROM courses WHERE id = ? LIMIT 1");
  $courseStmt->execute([$courseId]);
  $course = $courseStmt->fetch();

  if (!$course) {
    respond(404, ["ok" => false, "error" => "Course not found"]);
  }

  $price = (float)($course['price'] ?? 0);
  $instructorId = (int)($course['instructor_id'] ?? 0);

  // Teachers cannot enroll in their own courses
  if ($userId === $instructorId) {
    respond(403, ["ok" => false, "error" => "You cannot enroll in your own course"]);
  }

  // Check if already enrolled
  $checkStmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1");
  $checkStmt->execute([$userId, $courseId]);

  if ($checkStmt->fetch()) {
    respond(409, ["ok" => false, "error" => "Already enrolled in this course"]);
  }

  // For paid courses, check if payment exists
  if ($price > 0) {
    $paymentStmt = $pdo->prepare("
      SELECT id FROM course_purchases 
      WHERE user_id = ? AND course_id = ? AND status IN ('paid', 'pending')
      LIMIT 1
    ");
    $paymentStmt->execute([$userId, $courseId]);

    if (!$paymentStmt->fetch()) {
      respond(402, ["ok" => false, "error" => "Payment required", "requiresPayment" => true]);
    }
  }

  // Create enrollment
  $enrollStmt = $pdo->prepare("
    INSERT INTO enrollments (user_id, course_id, enrolled_at)
    VALUES (?, ?, NOW())
  ");
  $enrollStmt->execute([$userId, $courseId]);

  $enrollmentId = (int)$pdo->lastInsertId();

  // Initialize course progress
  $progressStmt = $pdo->prepare("
    INSERT INTO course_progress (user_id, course_id, progress_percent, lessons_done)
    VALUES (?, ?, 0, 0)
    ON DUPLICATE KEY UPDATE updated_at = NOW()
  ");
  $progressStmt->execute([$userId, $courseId]);

  respond(201, [
    "ok" => true,
    "enrollmentId" => $enrollmentId,
    "message" => "Successfully enrolled in course"
  ]);

} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
