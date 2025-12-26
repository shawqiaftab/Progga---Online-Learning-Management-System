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

$courseId = (int)($_GET["courseId"] ?? 0);
if ($courseId <= 0) respond(400, ["ok"=>false, "error"=>"Invalid courseId"]);

try {
  $own = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ? LIMIT 1");
  $own->execute([$courseId, $userId]);
  if (!$own->fetch()) respond(403, ["ok"=>false, "error"=>"Not allowed"]);

  $ex = $pdo->prepare("SELECT id, title, duration_minutes, passing_score, attempts_allowed FROM exams WHERE course_id = ? LIMIT 1");
  $ex->execute([$courseId]);
  $exam = $ex->fetch(PDO::FETCH_ASSOC);

  if (!$exam) {
    respond(200, ["ok"=>true, "exam"=>null]);
  }

  $examId = (int)$exam["id"];

  $qStmt = $pdo->prepare("
    SELECT id, question_text, sort_order
    FROM exam_questions
    WHERE exam_id = ?
    ORDER BY sort_order ASC, id ASC
  ");
  $qStmt->execute([$examId]);
  $qs = $qStmt->fetchAll(PDO::FETCH_ASSOC);

  $cStmt = $pdo->prepare("
    SELECT id, choice_label, choice_text, is_correct
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
        "label" => (string)$c["choice_label"],
        "text" => (string)$c["choice_text"],
        "isCorrect" => ((int)$c["is_correct"] === 1),
      ];
    }

    $questions[] = [
      "id" => $qid,
      "text" => (string)$q["question_text"],
      "sortOrder" => (int)$q["sort_order"],
      "choices" => $choices,
    ];
  }

  respond(200, [
    "ok"=>true,
    "exam"=>[
      "id"=>$examId,
      "title"=>(string)$exam["title"],
      "durationMinutes"=>(int)$exam["duration_minutes"],
      "passingScore"=>(float)$exam["passing_score"],
      "attemptsAllowed"=>(int)$exam["attempts_allowed"],
      "questions"=>$questions
    ]
  ]);
} catch (Throwable $e) {
  respond(500, ["ok"=>false, "error"=>"Server error"]);
}
