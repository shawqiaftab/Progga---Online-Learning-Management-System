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

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) respond(401, ["ok" => false, "error" => "Not logged in"]);

$attemptId = (int)($_GET["attemptId"] ?? $_GET["attemptid"] ?? $_GET["attempt_id"] ?? 0);
if ($attemptId <= 0) respond(400, ["ok" => false, "error" => "attemptId is required"]);

try {
  
  $stmt = $pdo->prepare("
    SELECT
      a.id AS attempt_id,
      a.exam_id,
      a.user_id,
      COALESCE(a.score_percent, 0) AS score_percent,
      COALESCE(a.correct, 0) AS correct_cnt,
      COALESCE(a.incorrect, 0) AS incorrect_cnt,
      COALESCE(a.total, 0) AS total_cnt,
      e.title AS exam_title,
      e.course_id,
      c.title AS course_title
    FROM exam_attempts a
    JOIN exams e   ON e.id = a.exam_id
    JOIN courses c ON c.id = e.course_id
    WHERE a.id = ? AND a.user_id = ?
    LIMIT 1
  ");
  $stmt->execute([$attemptId, $userId]);
  $meta = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$meta) respond(404, ["ok" => false, "error" => "Result not found"]);

  $examId = (int)$meta["exam_id"];

 
  $rowsStmt = $pdo->prepare("
    SELECT
      q.id AS question_id,
      q.question_text,
      q.sort_order,
      ch.id AS choice_id,
      ch.choice_label,
      ch.choice_text,
      COALESCE(ch.is_correct, 0) AS is_correct,
      aa.choice_id AS chosen_choice_id
    FROM exam_questions q
    JOIN exam_choices ch
      ON ch.question_id = q.id
    LEFT JOIN exam_attempt_answers aa
      ON aa.attempt_id = ? AND aa.question_id = q.id
    WHERE q.exam_id = ?
    ORDER BY q.sort_order ASC, q.id ASC, ch.id ASC
  ");
  $rowsStmt->execute([$attemptId, $examId]);
  $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

 
  $questions = [];
  foreach ($rows as $r) {
    $qid = (int)$r["question_id"];
    if (!isset($questions[$qid])) {
      $questions[$qid] = [
        "id" => $qid,
        "text" => (string)($r["question_text"] ?? ""),
        "explanation" => "", 
        "isCorrect" => false,
        "options" => []
      ];
    }

    $choiceId = (int)$r["choice_id"];
    $chosenId = (int)($r["chosen_choice_id"] ?? 0);
    $isCorrectChoice = ((int)$r["is_correct"] === 1);
    $isChosen = ($chosenId > 0 && $chosenId === $choiceId);

    if ($isChosen && $isCorrectChoice) {
      $questions[$qid]["isCorrect"] = true;
    }

    $questions[$qid]["options"][] = [
      "id" => $choiceId,
      "label" => (string)($r["choice_label"] ?? ""),
      "text" => (string)($r["choice_text"] ?? ""),
      "isCorrect" => $isCorrectChoice,
      "isChosen" => $isChosen
    ];
  }

  $questionsList = array_values($questions);

 
  $total = (int)$meta["total_cnt"];
  $correct = (int)$meta["correct_cnt"];
  $incorrect = (int)$meta["incorrect_cnt"];
  $score = (float)$meta["score_percent"];

  if ($total <= 0) {
    $totalStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM exam_questions WHERE exam_id = ?");
    $totalStmt->execute([$examId]);
    $total = (int)($totalStmt->fetch(PDO::FETCH_ASSOC)["cnt"] ?? 0);
  }

  if ($total > 0 && ($correct === 0 && $incorrect === 0 && $score == 0.0)) {
    $corrStmt = $pdo->prepare("
      SELECT COUNT(*) AS cnt
      FROM exam_attempt_answers aa
      JOIN exam_choices ch ON ch.id = aa.choice_id
      JOIN exam_questions q ON q.id = aa.question_id
      WHERE aa.attempt_id = ? AND q.exam_id = ? AND ch.is_correct = 1
    ");
    $corrStmt->execute([$attemptId, $examId]);
    $correct = (int)($corrStmt->fetch(PDO::FETCH_ASSOC)["cnt"] ?? 0);
    $incorrect = max(0, $total - $correct);
    $score = round(($correct / $total) * 100, 2);
  }

  $remark = ($score >= 80) ? "Excellent! Keep it up."
          : (($score >= 50) ? "Good effort. Keep practicing."
          : "Needs improvement. Review the lessons.");

  respond(200, [
    "ok" => true,
    "result" => [
      "attemptId" => (int)$meta["attempt_id"],
      "examId" => (int)$meta["exam_id"],
      "examTitle" => (string)$meta["exam_title"],
      "courseId" => (int)$meta["course_id"],
      "courseTitle" => (string)$meta["course_title"],
      "scorePercent" => (int)round((float)$score),
      "remark" => $remark,
      "correct" => $correct,
      "incorrect" => $incorrect,
      "total" => $total,
      "questions" => $questionsList
    ]
  ]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
