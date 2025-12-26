<?php
declare(strict_types=1);

require __DIR__ . "/../auth/guard.php"; 
require __DIR__ . "/../_lib/json.php";
require __DIR__ . "/../_lib/db.php";

$userId = (int)($_SESSION['user_id'] ?? 0);
$role = (string)($_SESSION['role'] ?? '');

if ($userId <= 0) json_error("Unauthorized", 401);
if (!in_array($role, ["teacher", "admin"], true)) json_error("Forbidden", 403);

try {
  $stmt = $pdo->prepare("
    SELECT id, title
    FROM courses
    WHERE teacher_id = ?
    ORDER BY id DESC
  ");
  $stmt->execute([$userId]);

  $courses = [];
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = [
      "id" => (int)$row["id"],
      "title" => (string)$row["title"],
    ];
  }

  json_ok(["ok" => true, "courses" => $courses]);

} catch (Throwable $e) {
  json_error("Server error", 500);
}
