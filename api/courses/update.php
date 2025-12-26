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

if ($_SERVER["REQUEST_METHOD"] !== "POST") respond(405, ["ok" => false, "error" => "Method not allowed"]);
if (empty($_SESSION["user_id"])) respond(401, ["ok" => false, "error" => "Not logged in"]);

$instructorId = (int)$_SESSION["user_id"];

$courseId = (int)($_POST["course_id"] ?? 0);
$title = trim((string)($_POST["title"] ?? ""));
$category = trim((string)($_POST["category"] ?? ""));
$description = trim((string)($_POST["description"] ?? ""));
$difficulty = trim((string)($_POST["difficulty"] ?? "Beginner"));
$totalLessons = (int)($_POST["lessons"] ?? 0);
$durationHours = ($_POST["duration"] ?? "") === "" ? null : (float)$_POST["duration"];
$isFree = ((string)($_POST["is_free"] ?? "0") === "1");
$price = (float)($_POST["price"] ?? 0);
if ($isFree) $price = 0.0;

if ($courseId <= 0) respond(400, ["ok" => false, "error" => "Invalid course_id"]);
if ($title === "" || $category === "" || $description === "") respond(400, ["ok" => false, "error" => "Title, category, description required"]);
if ($totalLessons <= 0) respond(400, ["ok" => false, "error" => "Total lessons must be at least 1"]);
if (!in_array($difficulty, ["Beginner", "Intermediate", "Advanced"], true)) $difficulty = "Beginner";

try {
  $chk = $pdo->prepare("SELECT thumbnail_url FROM courses WHERE id = ? AND instructor_id = ? LIMIT 1");
  $chk->execute([$courseId, $instructorId]);
  $row = $chk->fetch();
  if (!$row) respond(403, ["ok" => false, "error" => "Not allowed"]);

  $thumbnailUrl = (string)($row["thumbnail_url"] ?? "");


  if (isset($_FILES["thumbnail"]) && is_array($_FILES["thumbnail"]) && ($_FILES["thumbnail"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if (($_FILES["thumbnail"]["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
      respond(400, ["ok" => false, "error" => "Thumbnail upload failed"]);
    }

    $origName = (string)$_FILES["thumbnail"]["name"];
    $tmp = (string)$_FILES["thumbnail"]["tmp_name"];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp"];
    if (!in_array($ext, $allowed, true)) respond(400, ["ok" => false, "error" => "Thumbnail must be JPG/PNG/WEBP"]);

    $uploadDir = __DIR__ . "/../../uploads/course_thumbs";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $safeName = "course_" . time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
    $dest = $uploadDir . "/" . $safeName;

    if (!move_uploaded_file($tmp, $dest)) { 
      respond(500, ["ok" => false, "error" => "Failed to save thumbnail"]);
    }
    $thumbnailUrl = "/proj/uploads/course_thumbs/" . $safeName;
  }

  
  $upd = $pdo->prepare("
    UPDATE courses
    SET
      title = ?,
      category = ?,
      description = ?,
      difficulty = ?,
      total_lessons = ?,
      duration_hours = ?,
      price = ?,
      thumbnail_url = ?
    WHERE id = ? AND instructor_id = ?
  ");
  $upd->execute([
    $title,
    $category,
    $description,
    $difficulty,
    $totalLessons,
    $durationHours,
    $price,
    $thumbnailUrl,
    $courseId,
    $instructorId
  ]);

  respond(200, ["ok" => true, "courseId" => $courseId, "thumbnailUrl" => $thumbnailUrl]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
