<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

if (empty($_SESSION['user_id'])) {
  respond(401, ["ok" => false, "error" => "Not logged in"]);
}

$userId = (int)$_SESSION['user_id'];

try {
  // Get all enrollments with course details and progress
  $stmt = $pdo->prepare("
    SELECT 
      e.id AS enrollment_id,
      e.enrolled_at,
      c.id AS course_id,
      c.title,
      c.category,
      c.description,
      c.thumbnail_url,
      c.video_url,
      c.difficulty,
      c.total_lessons,
      c.duration_hours,
      c.price,
      c.instructor_id,
      u.name AS instructor_name,
      COALESCE(cp.progress_percent, 0) AS progress_percent,
      COALESCE(cp.lessons_done, 0) AS lessons_done
    FROM enrollments e
    INNER JOIN courses c ON c.id = e.course_id
    LEFT JOIN users u ON u.id = c.instructor_id
    LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
  ");

  $stmt->execute([$userId]);
  $rows = $stmt->fetchAll();

  $enrollments = [];
  foreach ($rows as $r) {
    $enrollments[] = [
      "enrollmentId" => (int)$r['enrollment_id'],
      "enrolledAt" => (string)$r['enrolled_at'],
      "course" => [
        "id" => (int)$r['course_id'],
        "title" => (string)$r['title'],
        "category" => (string)($r['category'] ?? ''),
        "description" => (string)($r['description'] ?? ''),
        "thumbnailUrl" => (string)($r['thumbnail_url'] ?? ''),
        "videoUrl" => (string)($r['video_url'] ?? ''),
        "difficulty" => (string)($r['difficulty'] ?? 'Beginner'),
        "totalLessons" => (int)($r['total_lessons'] ?? 0),
        "durationHours" => (float)($r['duration_hours'] ?? 0),
        "price" => (float)($r['price'] ?? 0),
        "instructor" => [
          "id" => (int)($r['instructor_id'] ?? 0),
          "name" => (string)($r['instructor_name'] ?? 'Unknown')
        ]
      ],
      "progress" => [
        "percent" => (float)$r['progress_percent'],
        "lessonsDone" => (int)$r['lessons_done']
      ]
    ];
  }

  respond(200, [
    "ok" => true,
    "enrollments" => $enrollments,
    "total" => count($enrollments)
  ]);

} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
