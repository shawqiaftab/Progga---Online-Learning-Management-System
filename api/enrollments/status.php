<?php
declare(strict_types=1);

session_start();
require __DIR__ . "/../../config/db.php";

header("Content-Type: application/json; charset=utf-8");

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

if (empty($_SESSION["user_id"])) {
  respond(401, ["ok" => false, "error" => "Not logged in"]);
}

$courseId = (int)($_GET["courseId"] ?? 0);
if ($courseId <= 0) respond(400, ["ok" => false, "error" => "Invalid courseId"]);

$userId = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1");
$stmt->execute([$userId, $courseId]);
$enrolled = (bool)$stmt->fetch();

respond(200, ["ok" => true, "enrolled" => $enrolled]);
