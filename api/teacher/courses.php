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

try {

  $stmt = $pdo->prepare("
    SELECT id, title, category, thumbnail_url, video_url
    FROM courses
    WHERE instructor_id = ?
    ORDER BY id DESC
  ");
  $stmt->execute([$userId]);

  $courses = [];
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = [
      "id" => (int)$r["id"],
      "title" => (string)$r["title"],
      "category" => (string)($r["category"] ?? ""),
      "thumbnailUrl" => (string)($r["thumbnail_url"] ?? ""),
      "videoUrl" => (string)($r["video_url"] ?? ""),
    ];
  }

  respond(200, ["ok" => true, "courses" => $courses]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
