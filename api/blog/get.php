<?php
declare(strict_types=1);
session_start();

require __DIR__ . "/../../config/db.php";

function respond(int $code, array $data): void {
  http_response_code($code);
  header("Content-Type: application/json");
  echo json_encode($data);
  exit;
}

if (empty($_SESSION["user_id"])) {
  respond(401, ["ok" => false, "error" => "Not logged in"]);
}


$courseId = (int)($_GET["id"] ?? $_GET["courseId"] ?? 0);

if ($courseId <= 0) {
  respond(400, ["ok" => false, "error" => "Invalid course ID"]);
}

try {
  
  $stmt = $pdo->prepare("
    SELECT id, instructor_id, title, category, description, thumbnail_url,
           difficulty, total_lessons,
           duration_hours, duration_weeks,
           price
    FROM courses
    WHERE id = ?
    LIMIT 1
  ");
  
  $stmt->execute([$courseId]);
  $c = $stmt->fetch();
  
  if (!$c) {
    respond(404, ["ok" => false, "error" => "Course not found"]);
  }
  
  $price = isset($c["price"]) ? (float)$c["price"] : 0.0;
  $isFree = $price <= 0;
  
  $duration = null;
  if (isset($c["duration_hours"]) && $c["duration_hours"] !== null) {
    $duration = (float)$c["duration_hours"];
  } else if (isset($c["duration_weeks"]) && $c["duration_weeks"] !== null) {
    $duration = (float)$c["duration_weeks"];
  }
  
  respond(200, [
    "ok" => true,
    "course" => [
      "id" => (int)$c["id"],
      "title" => (string)$c["title"],
      "category" => (string)($c["category"] ?? ""),
      "description" => (string)($c["description"] ?? ""),
      "difficulty" => (string)($c["difficulty"] ?? "Beginner"),
      "lessons" => (int)($c["total_lessons"] ?? 0),
      "duration" => $duration,
      "price" => $price,
      "isFree" => $isFree,
      "thumbnailUrl" => (string)($c["thumbnail_url"] ?? "")
    ]
  ]);
  
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
