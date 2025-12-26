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
  
  $sql = "
    SELECT
      c.id,
      c.title,
      c.description,
      c.category,
      c.difficulty,
      c.thumbnail_url,
      c.price AS original_price,
      COALESCE(d.discount_percent, 0) AS discount_percent
    FROM courses c
    LEFT JOIN course_discounts d
      ON d.course_id = c.id
      AND d.active = 1
      AND (d.starts_at IS NULL OR d.starts_at <= NOW())
      AND (d.ends_at IS NULL OR d.ends_at >= NOW())
    WHERE (c.price <= 0) OR (COALESCE(d.discount_percent, 0) > 0)
    ORDER BY (c.price <= 0) DESC, discount_percent DESC, c.id DESC
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $courses = [];
  foreach ($rows as $r) {
    $orig = (float)($r["original_price"] ?? 0);
    $disc = (float)($r["discount_percent"] ?? 0);
    $isFree = $orig <= 0;

    $discounted = $isFree ? 0 : max(0, round($orig - ($orig * $disc / 100), 2));

    $courses[] = [
      "id" => (int)$r["id"],
      "title" => (string)$r["title"],
      "description" => (string)($r["description"] ?? ""),
      "category" => (string)($r["category"] ?? ""),
      "difficulty" => (string)($r["difficulty"] ?? ""),
      "isFree" => $isFree,
      "originalPrice" => $isFree ? 0 : $orig,
      "discountPercent" => $isFree ? 0 : $disc,
      "discountedPrice" => $isFree ? 0 : $discounted,
      "image" => (string)($r["thumbnail_url"] ?? ""),
    ];
  }

  respond(200, ["ok" => true, "courses" => $courses]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
