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

if (empty($_SESSION["user_id"])) respond(401, ["ok" => false, "error" => "Not logged in"]);
$userId = (int)$_SESSION["user_id"];

$body = json_decode(file_get_contents("php://input"), true) ?: [];
$courseId = (int)($body["courseId"] ?? 0);
$videoUrl = trim((string)($body["videoUrl"] ?? ""));

if ($courseId <= 0) respond(400, ["ok" => false, "error" => "Invalid courseId"]);

if ($videoUrl !== "" && strlen($videoUrl) > 500) respond(400, ["ok" => false, "error" => "videoUrl too long"]);

try {
  $own = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ? LIMIT 1");
  $own->execute([$courseId, $userId]);
  if (!$own->fetch()) respond(403, ["ok" => false, "error" => "Not allowed"]);

  $upd = $pdo->prepare("UPDATE courses SET video_url = ? WHERE id = ? AND instructor_id = ?");
  $upd->execute([$videoUrl === "" ? null : $videoUrl, $courseId, $userId]);

  respond(200, ["ok" => true]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
