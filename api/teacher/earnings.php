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

if (($_SESSION['role'] ?? '') !== 'teacher') {
  respond(403, ["ok" => false, "error" => "Only teachers can view earnings"]);
}

$instructorId = (int)$_SESSION['user_id'];

// Get first and last day of current month
$firstDay = (new DateTime('first day of this month 00:00:00'))->format('Y-m-d H:i:s');
$lastDay = (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

try {
  // Total earnings and sales (FIXED: payments -> course_purchases)
  $q1 = $pdo->prepare("
    SELECT
      COALESCE(SUM(p.amount),0) AS total_earnings,
      COUNT(p.id) AS total_sales
    FROM course_purchases p
    INNER JOIN courses c ON c.id = p.course_id
    WHERE c.instructor_id = ?
      AND p.status IN ('paid','pending')
  ");
  $q1->execute([$instructorId]);
  $agg = $q1->fetch();

  // This month earnings (FIXED: payments -> course_purchases)
  $q2 = $pdo->prepare("
    SELECT COALESCE(SUM(p.amount),0) AS month_earnings
    FROM course_purchases p
    INNER JOIN courses c ON c.id = p.course_id
    WHERE c.instructor_id = ?
      AND p.status IN ('paid','pending')
      AND p.created_at BETWEEN ? AND ?
  ");
  $q2->execute([$instructorId, $firstDay, $lastDay]);
  $m = $q2->fetch();

  // Pending payout (FIXED: payments -> course_purchases)
  $q3 = $pdo->prepare("
    SELECT COALESCE(SUM(p.amount),0) AS pending_amount
    FROM course_purchases p
    INNER JOIN courses c ON c.id = p.course_id
    WHERE c.instructor_id = ?
      AND p.status = 'pending'
  ");
  $q3->execute([$instructorId]);
  $pend = $q3->fetch();

  // Earnings by course (FIXED: payments -> course_purchases)
  $q4 = $pdo->prepare("
    SELECT
      c.id AS course_id,
      c.title AS course_title,
      COUNT(DISTINCT e.user_id) AS students,
      COALESCE(SUM(p.amount),0) AS total_earned,
      MAX(p.status) AS max_status
    FROM courses c
    LEFT JOIN enrollments e ON e.course_id = c.id
    LEFT JOIN course_purchases p ON p.course_id = c.id
    WHERE c.instructor_id = ?
    GROUP BY c.id, c.title
    ORDER BY total_earned DESC, students DESC
  ");
  $q4->execute([$instructorId]);
  $rows = $q4->fetchAll();

  $byCourse = [];
  foreach ($rows as $r) {
    $status = ($r['max_status'] === 'pending') ? 'Pending' : 'Paid';
    $byCourse[] = [
      "courseId" => (int)$r['course_id'],
      "courseTitle" => (string)$r['course_title'],
      "students" => (int)$r['students'],
      "totalEarned" => (float)$r['total_earned'],
      "status" => $status
    ];
  }

  $summary = [
    "totalEarnings" => (float)($agg['total_earnings'] ?? 0),
    "thisMonthEarnings" => (float)($m['month_earnings'] ?? 0),
    "thisMonthLabel" => date('M Y'),
    "pendingPayout" => (float)($pend['pending_amount'] ?? 0),
    "nextPayoutLabel" => "5th of next month",
    "totalSales" => (int)($agg['total_sales'] ?? 0)
  ];

  respond(200, ["ok" => true, "summary" => $summary, "byCourse" => $byCourse]);
} catch (Throwable $e) {
  respond(500, ["ok" => false, "error" => "Server error"]);
}
