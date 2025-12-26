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

$courseId = (int)($_GET["courseId"] ?? $_GET["id"] ?? 0);
$difficulty = strtolower((string)($_GET["difficulty"] ?? "easy"));

if ($courseId <= 0) respond(400, ["ok"=>false, "error"=>"Invalid courseId"]);

try {
  $ex = $pdo->prepare("
    SELECT id, title, duration_minutes, passing_score, attempts_allowed
    FROM exams
    WHERE course_id = ?
    LIMIT 1
  ");
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
  $attemptsUsed = (int)($usedStmt->fetch(PDO::FETCH_ASSOC)["cnt"] ?? 0);

 
  $qStmt = $pdo->prepare("
    SELECT id, question_text
    FROM exam_questions
    WHERE exam_id = ?
    ORDER BY sort_order ASC, id ASC
  ");
  $qStmt->execute([$examId]);
  $qs = $qStmt->fetchAll(PDO::FETCH_ASSOC);

 
  $cStmt = $pdo->prepare("
    SELECT id, choice_label, choice_text
    FROM exam_choices
    WHERE question_id = ?
    ORDER BY id ASC
  ");

  $questions = [];
  foreach ($qs as $q) {
    $qid = (int)$q["id"];
    $cStmt->execute([$qid]);
    $choicesRaw = $cStmt->fetchAll(PDO::FETCH_ASSOC);

    $choices = [];
    foreach ($choicesRaw as $c) {
      $choices[] = [
        "id" => (int)$c["id"],
        "label" => (string)($c["choice_label"] ?? ""),
        "text" => (string)($c["choice_text"] ?? ""),
      ];
    }

    $questions[] = [
      "id" => $qid,
      "text" => (string)($q["question_text"] ?? ""),
      "choices" => $choices,
    ];
  }


  respond(200, [
    "ok" => true,
    "difficulty" => $difficulty,
    "attemptsUsed" => $attemptsUsed,
    "exam" => [
      "id" => $examId,
      "title" => (string)($exam["title"] ?? "Final Exam"),
      "durationMinutes" => (int)($exam["duration_minutes"] ?? 0),
      "passingScore" => (float)($exam["passing_score"] ?? 50),
      "attemptsAllowed" => (int)($exam["attempts_allowed"] ?? 1),
      "questions" => $questions,
    ],
  ]);

} catch (Throwable $e) {
  respond(500, ["ok"=>false, "error"=>"Server error"]);
}
