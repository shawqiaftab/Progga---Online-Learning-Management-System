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
$questionText = trim((string)($body["questionText"] ?? ""));
$choices = $body["choices"] ?? [];

if ($courseId <= 0) respond(400, ["ok" => false, "error" => "Invalid courseId"]);
if ($questionText === "") respond(400, ["ok" => false, "error" => "Question text required"]);
if (!is_array($choices) || count($choices) < 2) respond(400, ["ok" => false, "error" => "Choices required"]);

$correctCount = 0;
$seenLabels = [];
foreach ($choices as $ch) {
  $label = strtoupper(trim((string)($ch["label"] ?? "")));
  $text = trim((string)($ch["text"] ?? ""));
  $isCorrect = !empty($ch["isCorrect"]) ? 1 : 0;

  if ($label === "" || strlen($label) !== 1) respond(400, ["ok" => false, "error" => "Invalid choice label"]);
  if (isset($seenLabels[$label])) respond(400, ["ok" => false, "error" => "Duplicate choice label"]);
  if ($text === "") respond(400, ["ok" => false, "error" => "Choice text required"]);

  $seenLabels[$label] = true;
  $correctCount += $isCorrect;
}
if ($correctCount !== 1) respond(400, ["ok" => false, "error" => "Exactly 1 correct choice required"]);

try {
  $own = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ? LIMIT 1");
  $own->execute([$courseId, $userId]);
  if (!$own->fetch()) respond(403, ["ok" => false, "error" => "Not allowed"]);

  $ex = $pdo->prepare("SELECT id FROM exams WHERE course_id = ? LIMIT 1");
  $ex->execute([$courseId]);
  $exam = $ex->fetch(PDO::FETCH_ASSOC);
  if (!$exam) respond(400, ["ok" => false, "error" => "Create exam settings first"]);
  $examId = (int)$exam["id"];

  $maxSort = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) AS mx FROM exam_questions WHERE exam_id = ?");
  $maxSort->execute([$examId]);
  $nextSort = (int)($maxSort->fetch(PDO::FETCH_ASSOC)["mx"] ?? 0) + 1;

  $pdo->beginTransaction();

  $insQ = $pdo->prepare("INSERT INTO exam_questions (exam_id, question_text, sort_order) VALUES (?, ?, ?)");
  $insQ->execute([$examId, $questionText, $nextSort]);
  $questionId = (int)$pdo->lastInsertId();

  $insC = $pdo->prepare("INSERT INTO exam_choices (question_id, choice_label, choice_text, is_correct) VALUES (?, ?, ?, ?)");
  foreach ($choices as $ch) {
    $label = strtoupper(trim((string)$ch["label"]));
    $text = trim((string)$ch["text"]);
    $isCorrect = !empty($ch["isCorrect"]) ? 1 : 0;
    $insC->execute([$questionId, $label, $text, $isCorrect]);
  }

  $pdo->commit();
  respond(200, ["ok" => true, "questionId" => $questionId]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ["ok" => false, "error" => "Server error"]);
}
