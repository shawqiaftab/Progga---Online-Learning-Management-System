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
$questionId = (int)($body["questionId"] ?? 0);

if ($courseId <= 0 || $questionId <= 0) respond(400, ["ok" => false, "error" => "Invalid payload"]);

try {

  $chk = $pdo->prepare("
    SELECT q.id
    FROM exam_questions q
    JOIN exams e ON e.id = q.exam_id
    JOIN courses c ON c.id = e.course_id
    WHERE q.id = ? AND e.course_id = ? AND c.instructor_id = ?
    LIMIT 1
  ");
  $chk->execute([$questionId, $courseId, $userId]);
  if (!$chk->fetch()) respond(403, ["ok" => false, "error" => "Not allowed"]);

  $pdo->beginTransaction();
  $delC = $pdo->prepare("DELETE FROM exam_choices WHERE question_id = ?");
  $delC->execute([$questionId]);

  $delQ = $pdo->prepare("DELETE FROM exam_questions WHERE id = ?");
  $delQ->execute([$questionId]);

  $pdo->commit();
  respond(200, ["ok" => true]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ["ok" => false, "error" => "Server error"]);
}
