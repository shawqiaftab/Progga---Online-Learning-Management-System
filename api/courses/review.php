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

if (empty($_SESSION["user_id"])) {
  respond(401, ["ok" => false, "error" => "Not logged in"]);
}

$userId = (int)$_SESSION["user_id"];
$courseId = (int)($_GET["courseId"] ?? 0);
if ($courseId <= 0) {
  respond(400, ["ok" => false, "error" => "Invalid courseId"]);
}

try {

  $stmt = $pdo->prepare("
    SELECT id, title, thumbnail_url, difficulty, total_lessons
    FROM courses
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->execute([$courseId]); 
  $course = $stmt->fetch();
  if (!$course) respond(404, ["ok" => false, "error" => "Course not found"]);

  
  $en = $pdo->prepare("SELECT enrolled_at FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1");
  $en->execute([$userId, $courseId]);
  $enRow = $en->fetch();
  if (!$enRow) respond(403, ["ok" => false, "error" => "Not enrolled"]);

  
  $lessons = [];
  $hasLessonsTable = true;

  try {
    $ls = $pdo->prepare("
      SELECT id, title, type, duration_minutes
      FROM course_lessons
      WHERE course_id = ?
      ORDER BY id ASC
    ");
    $ls->execute([$courseId]);
    $lessonRows = $ls->fetchAll();

  
    foreach ($lessonRows as $r) {
      $lessons[] = [
        "id" => (int)$r["id"],
        "title" => (string)$r["title"],
        "type" => (string)($r["type"] ?? "video"),
        "durationMinutes" => $r["duration_minutes"] !== null ? (int)$r["duration_minutes"] : null,
        "completed" => false
      ];
    }

    
    try {
      $pr = $pdo->prepare("
        SELECT lesson_id
        FROM lesson_progress
        WHERE user_id = ? AND course_id = ? AND completed = 1
      ");
      $pr->execute([$userId, $courseId]);
      $done = array_flip(array_map(fn($x) => (int)$x["lesson_id"], $pr->fetchAll()));
      foreach ($lessons as &$l) {
        $l["completed"] = isset($done[$l["id"]]);
      }
      unset($l);
    } catch (Throwable $e) {
  
    }
  } catch (Throwable $e) {
    $hasLessonsTable = false;
  }

  if (!$hasLessonsTable) {
   
    $n = (int)($course["total_lessons"] ?? 0);
    for ($i = 1; $i <= max(0, $n); $i++) {
      $lessons[] = [
        "id" => $i,
        "title" => "Lesson " . $i,
        "type" => "video",
        "durationMinutes" => null,
        "completed" => true
      ];
    }
  }

  
  $exam = ["attempted" => false];
  try {
    $ex = $pdo->prepare("
      SELECT exam_name, score_percent, correct, total_questions, taken_at
      FROM exam_attempts
      WHERE user_id = ? AND course_id = ?
      ORDER BY taken_at DESC
      LIMIT 1
    ");
    $ex->execute([$userId, $courseId]);
    $exRow = $ex->fetch();
    if ($exRow) {
      $exam = [
        "attempted" => true,
        "name" => (string)($exRow["exam_name"] ?? "Final Exam"),
        "scorePercent" => (int)($exRow["score_percent"] ?? 0),
        "correct" => (int)($exRow["correct"] ?? 0),
        "totalQuestions" => (int)($exRow["total_questions"] ?? 0),
        "takenAt" => (string)($exRow["taken_at"] ?? ""),
        "message" => ""
      ];
    }
  } catch (Throwable $e) {
  
  }

  respond(200, [
    "ok" => true,
    "course" => [
      "id" => (int)$course["id"],
      "title" => (string)$course["title"],
      "image" => (string)($course["thumbnail_url"] ?: ""),
      "difficulty" => (string)($course["difficulty"] ?: ""),
      "lessonsCount" => (int)($course["total_lessons"] ?? 0),
      "completedOn" => null
    ],
    "lessons" => $lessons,
    "exam" => $exam
  ]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
