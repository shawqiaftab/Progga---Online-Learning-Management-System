<?php

declare(strict_types=1);

require __DIR__ . "/../auth/guard.php";
require __DIR__ . "/../_lib/json.php";
require __DIR__ . "/../_lib/db.php";

$userId = (int)($_SESSION['user_id'] ?? 0);
$role = (string)($_SESSION['role'] ?? '');

if ($userId <= 0) json_error("Unauthorized", 401);
if (!in_array($role, ["teacher", "admin"], true)) json_error("Forbidden", 403);

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($courseId <= 0) json_error("course_id is required", 400);

try {

  $cStmt = $pdo->prepare("SELECT id, title, teacher_id FROM courses WHERE id = ? LIMIT 1");
  $cStmt->execute([$courseId]);
  $course = $cStmt->fetch(PDO::FETCH_ASSOC);
  if (!$course) json_error("Course not found", 404);
  if ($role !== "admin" && (int)$course["teacher_id"] !== $userId) json_error("Forbidden", 403);


  $sStmt = $pdo->prepare("
    SELECT
      COUNT(*) AS total_reviews,
      AVG(overall_rating) AS overall_avg,
      AVG(content_rating) AS content_avg,
      AVG(clarity_rating) AS clarity_avg
    FROM course_reviews
    WHERE course_id = ?
  ");
  $sStmt->execute([$courseId]);
  $s = $sStmt->fetch(PDO::FETCH_ASSOC) ?: [];

 
  $rStmt = $pdo->prepare("
    SELECT
      r.id,
      r.overall_rating,
      r.comment,
      r.created_at,
      u.name AS reviewer_name
    FROM course_reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.course_id = ?
    ORDER BY r.created_at DESC
    LIMIT 200
  ");
  $rStmt->execute([$courseId]);

  $reviews = [];
  while ($row = $rStmt->fetch(PDO::FETCH_ASSOC)) {
    $created = (string)($row["created_at"] ?? "");
    $human = $created ? date("F j, Y", strtotime($created)) : ""; 

    $reviews[] = [
      "id" => (int)$row["id"],
      "reviewerName" => (string)($row["reviewer_name"] ?? "Anonymous"),
      "createdAtHuman" => $human,
      "overallRating" => (float)($row["overall_rating"] ?? 0),
      "comment" => (string)($row["comment"] ?? ""),
    ];
  }

  json_ok([
    "ok" => true,
    "courseTitle" => (string)$course["title"],
    "summary" => [
      "totalReviews" => (int)($s["total_reviews"] ?? 0),
      "overallAvg" => (float)($s["overall_avg"] ?? 0),
      "contentAvg" => (float)($s["content_avg"] ?? 0),
      "clarityAvg" => (float)($s["clarity_avg"] ?? 0),
    ],
    "reviews" => $reviews,
  ]);
} catch (Throwable $e) {
  json_error("Server error", 500);
}
