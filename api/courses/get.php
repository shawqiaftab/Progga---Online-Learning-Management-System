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

$courseId = (int)($_GET["courseId"] ?? $_GET["id"] ?? 0);
if ($courseId <= 0) respond(400, ["ok"=>false, "error"=>"Invalid courseId"]);

try {
  $stmt = $pdo->prepare("
    SELECT
      c.id,
      c.instructor_id,
      c.title,
      c.category,
      c.description,
      c.thumbnail_url,
      c.video_url,
      c.difficulty,
      c.total_lessons,
      c.duration_hours,
      c.duration_weeks,
      c.price,
      c.learn_points,
      u.name AS teacher_name
    FROM courses c
    LEFT JOIN users u ON u.id = c.instructor_id
    WHERE c.id = ?
    LIMIT 1
  ");
  $stmt->execute([$courseId]);
  $c = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$c) respond(404, ["ok"=>false, "error"=>"Course not found"]);

  
  $learnPointsRaw = (string)($c["learn_points"] ?? "");
  $learnPoints = [];
  if ($learnPointsRaw !== "") {
    $asJson = json_decode($learnPointsRaw, true);
    if (is_array($asJson)) {
      foreach ($asJson as $p) {
        $p = trim((string)$p);
        if ($p !== "") $learnPoints[] = $p;
      }
    } else {
      foreach (preg_split("/\r\n|\n|\r/", $learnPointsRaw) as $p) {
        $p = trim($p);
        if ($p !== "") $learnPoints[] = $p;
      }
    }
  }

  $price = (float)($c["price"] ?? 0);

  respond(200, [
    "ok" => true,
    "course" => [
      "id" => (int)$c["id"],
      "title" => (string)$c["title"],
      "category" => (string)($c["category"] ?? ""),
      "description" => (string)($c["description"] ?? ""),
      "difficulty" => (string)($c["difficulty"] ?? "Beginner"),
      "thumbnailUrl" => (string)($c["thumbnail_url"] ?? ""),
      "videoUrl" => (string)($c["video_url"] ?? ""),
      "teacherName" => (string)($c["teacher_name"] ?? ""),
      "lessonsCount" => (int)($c["total_lessons"] ?? 0),
      "durationWeeks" => $c["duration_weeks"] !== null ? (int)$c["duration_weeks"] : null,
      "durationHours" => $c["duration_hours"] !== null ? (float)$c["duration_hours"] : null,
      "price" => $price,
      "isFree" => ($price <= 0),
      "learnPoints" => $learnPoints,
    ],
  ]);
} catch (Throwable $e) {
  respond(500, ["ok"=>false, "error"=>"Server error"]);
}
