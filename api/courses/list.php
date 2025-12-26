<?php
declare(strict_types=1);

require __DIR__ . "/../../config/db.php";

header("Content-Type: application/json; charset=utf-8");

function respond(int $code, array $payload): void {
  http_response_code($code); 
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  
  $stmt = $pdo->prepare("
    SELECT
      id,
      title,
      description,
      category,
      difficulty,
      total_lessons,
      thumbnail_url,
      price
    FROM courses
    ORDER BY id DESC
  ");
  $stmt->execute(); 
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); 

  $courses = [];
  foreach ($rows as $r) {
    $price = $r["price"] ?? null;
    $isFree = ($price === null) ? true : ((float)$price <= 0);

    $courses[] = [
      "id" => (int)$r["id"],
      "title" => (string)$r["title"],
      "description" => (string)($r["description"] ?? ""),
      "category" => (string)($r["category"] ?? ""),
      "difficulty" => (string)($r["difficulty"] ?? ""),
      "lessonsCount" => (int)($r["total_lessons"] ?? 0),
      "image" => (string)($r["thumbnail_url"] ?? ""),
      "price" => $price === null ? null : (float)$price,
      "isFree" => $isFree
    ];
  }

  respond(200, ["ok" => true, "courses" => $courses]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
