<?php

declare(strict_types=1);

session_start();
require __DIR__ . "/../../config/db.php";

header("Content-Type: application/json; charset=utf-8");

function respond(int $code, array $payload): void
{
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  respond(405, ["ok" => false, "error" => "Method not allowed"]);
}
if (empty($_SESSION["user_id"])) {
  respond(401, ["ok" => false, "error" => "Not logged in"]);
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$courseId = (int)($data["courseId"] ?? 0);
if ($courseId <= 0) respond(400, ["ok" => false, "error" => "Invalid courseId"]);

$userId = (int)$_SESSION["user_id"];

try {

  $chk = $pdo->prepare("SELECT id FROM courses WHERE id = ? LIMIT 1");
  $chk->execute([$courseId]);
  if (!$chk->fetch()) respond(404, ["ok" => false, "error" => "Course not found"]);


  $ins = $pdo->prepare("INSERT IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)");
  $ins->execute([$userId, $courseId]);

  respond(200, ["ok" => true, "enrolled" => true]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
