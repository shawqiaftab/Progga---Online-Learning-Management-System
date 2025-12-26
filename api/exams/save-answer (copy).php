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

if (empty($_SESSION["user_id"])) respond(401, ["ok"=>false,"error"=>"Not logged in"]);
$userId = (int)$_SESSION["user_id"];

$body = json_decode(file_get_contents("php://input"), true);
$attemptId = (int)($body["attemptId"] ?? 0);
$questionId = (int)($body["questionId"] ?? 0);
$choiceId = (int)($body["choiceId"] ?? 0);
if ($attemptId<=0 || $questionId<=0 || $choiceId<=0) respond(400, ["ok"=>false,"error"=>"Invalid payload"]);

try {
  $chk = $pdo->prepare("SELECT ends_at, submitted_at FROM exam_attempts WHERE id = ? AND user_id = ? LIMIT 1");
  $chk->execute([$attemptId, $userId]);
  $a = $chk->fetch();
  if (!$a) respond(403, ["ok"=>false,"error"=>"Not allowed"]);
  if (!empty($a["submitted_at"])) respond(400, ["ok"=>false,"error"=>"Attempt already submitted"]);


  if (time() > strtotime((string)$a["ends_at"])) respond(400, ["ok"=>false,"error"=>"Time over"]);


  $ins = $pdo->prepare("
    INSERT INTO exam_attempt_answers (attempt_id, question_id, choice_id)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE choice_id = VALUES(choice_id)
  ");
  $ins->execute([$attemptId, $questionId, $choiceId]);

  respond(200, ["ok"=>true]);
} catch (Throwable $e) {
  respond(500, ["ok"=>false,"error"=>"Server error"]);
}
