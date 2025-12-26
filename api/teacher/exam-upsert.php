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
$title = trim((string)($body["title"] ?? "Final Exam"));
$durationMinutes = (int)($body["durationMinutes"] ?? 30);
$passingScore = (float)($body["passingScore"] ?? 50);
$attemptsAllowed = (int)($body["attemptsAllowed"] ?? 1);

if ($courseId <= 0) respond(400, ["ok" => false, "error" => "Invalid courseId"]);
if ($durationMinutes < 1 || $durationMinutes > 300) respond(400, ["ok" => false, "error" => "Invalid durationMinutes"]);
if ($passingScore < 0 || $passingScore > 100) respond(400, ["ok" => false, "error" => "Invalid passingScore"]);
if ($attemptsAllowed < 1 || $attemptsAllowed > 50) respond(400, ["ok" => false, "error" => "Invalid attemptsAllowed"]);
if ($title === "" || strlen($title) > 255) respond(400, ["ok" => false, "error" => "Invalid title"]);

try {
  $own = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ? LIMIT 1");
  $own->execute([$courseId, $userId]);
  if (!$own->fetch()) respond(403, ["ok" => false, "error" => "Not allowed"]);

  $chk = $pdo->prepare("SELECT id FROM exams WHERE course_id = ? LIMIT 1");
  $chk->execute([$courseId]);
  $ex = $chk->fetch(PDO::FETCH_ASSOC);

  if ($ex) {
    $upd = $pdo->prepare("
      UPDATE exams
      SET title = ?, duration_minutes = ?, passing_score = ?, attempts_allowed = ?
      WHERE id = ?
    ");
    $upd->execute([$title, $durationMinutes, $passingScore, $attemptsAllowed, (int)$ex["id"]]);
    $examId = (int)$ex["id"];
  } else {
    $ins = $pdo->prepare("
      INSERT INTO exams (course_id, title, duration_minutes, passing_score, attempts_allowed)
      VALUES (?, ?, ?, ?, ?)
    ");
    $ins->execute([$courseId, $title, $durationMinutes, $passingScore, $attemptsAllowed]);
    $examId = (int)$pdo->lastInsertId();
  }

  respond(200, ["ok" => true, "examId" => $examId]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
