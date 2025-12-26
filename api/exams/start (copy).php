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

if (empty($_SESSION["user_id"])) respond(401, ["ok"=>false, "error"=>"Not logged in"]);
$userId = (int)$_SESSION["user_id"];

$body = json_decode(file_get_contents("php://input"), true) ?: [];
$courseId = (int)($body["courseId"] ?? 0);
$difficulty = strtolower((string)($body["difficulty"] ?? "easy")); // optional

if ($courseId <= 0) respond(400, ["ok"=>false, "error"=>"Invalid courseId"]);

try {
  $ex = $pdo->prepare("SELECT id, duration_minutes, attempts_allowed FROM exams WHERE course_id = ? LIMIT 1");
  $ex->execute([$courseId]);
  $exam = $ex->fetch(PDO::FETCH_ASSOC);
  if (!$exam) respond(404, ["ok"=>false, "error"=>"No exam configured for this course"]);

  $examId = (int)$exam["id"];

  $usedStmt = $pdo->prepare("
    SELECT COUNT(*) AS cnt
    FROM exam_attempts
    WHERE exam_id = ? AND user_id = ? AND submitted_at IS NOT NULL
  ");
  $usedStmt->execute([$examId, $userId]);
  $used = (int)($usedStmt->fetch(PDO::FETCH_ASSOC)["cnt"] ?? 0);

  if ($used >= (int)$exam["attempts_allowed"]) {
    respond(403, ["ok"=>false, "error"=>"No attempts left"]);
  }

  $resume = $pdo->prepare("
    SELECT id, ends_at
    FROM exam_attempts
    WHERE exam_id = ? AND user_id = ? AND submitted_at IS NULL
    ORDER BY id DESC
    LIMIT 1
  ");
  $resume->execute([$examId, $userId]);
  $attempt = $resume->fetch(PDO::FETCH_ASSOC);

  if ($attempt) {
    $attemptId = (int)$attempt["id"];
    $endsAt = (string)$attempt["ends_at"];
  } else {
    $startedAt = (new DateTime("now"))->format("Y-m-d H:i:s");
    $endsAt = (new DateTime("now"))
      ->modify("+" . (int)$exam["duration_minutes"] . " minutes")
      ->format("Y-m-d H:i:s");

    $ins = $pdo->prepare("INSERT INTO exam_attempts (exam_id, user_id, started_at, ends_at) VALUES (?, ?, ?, ?)");
    $ins->execute([$examId, $userId, $startedAt, $endsAt]);
    $attemptId = (int)$pdo->lastInsertId();
  }

  $aStmt = $pdo->prepare("SELECT question_id, choice_id FROM exam_attempt_answers WHERE attempt_id = ?");
  $aStmt->execute([$attemptId]);

  $answers = [];
  foreach ($aStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $answers[(int)$r["question_id"]] = (int)$r["choice_id"];
  }

  $endsIso = (new DateTime($endsAt))->format(DateTime::ATOM);
  respond(200, ["ok"=>true, "attemptId"=>$attemptId, "endsAt"=>$endsIso, "answers"=>$answers, "difficulty"=>$difficulty]);

} catch (Throwable $e) {
  respond(500, ["ok"=>false, "error"=>"Server error"]);
}
