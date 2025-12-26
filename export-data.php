<?php

declare(strict_types=1);

session_start();
if (empty($_SESSION["user_id"])) {
  header("Location: /proj/login.html");
  exit;
}

require __DIR__ . "/config/db.php";

$userId = (int)$_SESSION["user_id"];
$format = strtolower((string)($_GET["format"] ?? "json"));
if (!in_array($format, ["json", "csv"], true)) $format = "json";


function buildExportData(PDO $pdo, int $userId): array
{
  $u = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ? LIMIT 1");
  $u->execute([$userId]);
  $user = $u->fetch(PDO::FETCH_ASSOC);


  $enrollments = [];
  try {
    $e = $pdo->prepare("
      SELECT
        e.id,
        e.course_id,
        e.enrolled_at,
        c.title AS course_title
      FROM enrollments e
      LEFT JOIN courses c ON c.id = e.course_id
      WHERE e.user_id = ?
      ORDER BY e.id DESC
    ");
    $e->execute([$userId]);
    $enrollments = $e->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $ignored) {
  }


  $myCourses = [];
  try {
    $c = $pdo->prepare("
      SELECT
        id, title, category, difficulty, total_lessons, duration_hours, duration_weeks, price, created_at
      FROM courses
      WHERE instructor_id = ?
      ORDER BY id DESC
    ");
    $c->execute([$userId]);
    $myCourses = $c->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $ignored) {
  }

  return [
    "exported_at" => gmdate("c"),
    "user" => $user ?: ["id" => $userId],
    "enrollments" => $enrollments,
    "my_courses" => $myCourses
  ];
}

$data = buildExportData($pdo, $userId);

if (isset($_GET["download"]) && $_GET["download"] === "1") {
  $safeUser = "user_" . $userId;
  $stamp = date("Ymd_His");

  if ($format === "json") {
    $filename = "{$safeUser}_export_{$stamp}.json";
    header("Content-Type: application/json; charset=utf-8");
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
  }


  $filename = "{$safeUser}_export_{$stamp}.csv";
  header("Content-Type: text/csv; charset=utf-8");
  header('Content-Disposition: attachment; filename="' . $filename . '"');

  $out = fopen("php://output", "w");


  fputcsv($out, ["SECTION", "user"]);
  fputcsv($out, ["id", "name", "email", "created_at"]);
  fputcsv($out, [
    $data["user"]["id"] ?? "",
    $data["user"]["name"] ?? "",
    $data["user"]["email"] ?? "",
    $data["user"]["created_at"] ?? ""
  ]);
  fputcsv($out, []);


  fputcsv($out, ["SECTION", "enrollments"]);
  fputcsv($out, ["id", "course_id", "course_title", "enrolled_at"]);
  foreach (($data["enrollments"] ?? []) as $r) {
    fputcsv($out, [
      $r["id"] ?? "",
      $r["course_id"] ?? "",
      $r["course_title"] ?? "",
      $r["enrolled_at"] ?? ""
    ]);
  }
  fputcsv($out, []);


  fputcsv($out, ["SECTION", "my_courses"]);
  fputcsv($out, ["id", "title", "category", "difficulty", "total_lessons", "duration_hours", "duration_weeks", "price", "created_at"]);
  foreach (($data["my_courses"] ?? []) as $r) {
    fputcsv($out, [
      $r["id"] ?? "",
      $r["title"] ?? "",
      $r["category"] ?? "",
      $r["difficulty"] ?? "",
      $r["total_lessons"] ?? "",
      $r["duration_hours"] ?? "",
      $r["duration_weeks"] ?? "",
      $r["price"] ?? "",
      $r["created_at"] ?? ""
    ]);
  }

  fclose($out);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Export My Data</title>
  <style>
    :root {
      --primary-color: #adca9d;
    }

    body {
      margin: 0;
      font-family: 'Poppins', Arial, sans-serif;
      background: #fff;
    }

    .wrap {
      width: 90%;
      max-width: 900px;
      margin: 40px auto;
    }

    .card {
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 24px;
      background: #fafafa;
    }

    h1 {
      margin: 0 0 10px;
      color: #333;
    }

    p {
      color: #555;
      line-height: 1.6;
    }

    .row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 18px;
    }

    .btn {
      padding: 12px 18px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      text-decoration: none;
      font-weight: 700;
      display: inline-block;
    }

    .btn-primary {
      background: #2e6d3f;
      color: white;
    }

    .btn-outline {
      background: transparent;
      border: 2px solid #2e6d3f;
      color: #2e6d3f;
    }

    .note {
      margin-top: 14px;
      font-size: 13px;
      color: #777;
    }

    code {
      background: #fff;
      border: 1px solid #eee;
      padding: 2px 6px;
      border-radius: 6px;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="card">
      <h1>Export your data</h1>
      <p>Download a copy of your account data in JSON or CSV format.</p>

      <div class="row">
        <a class="btn btn-primary" href="/proj/export-data.php?format=json&download=1">Download JSON</a>
        <a class="btn btn-outline" href="/proj/export-data.php?format=csv&download=1">Download CSV</a>
        <a class="btn btn-outline" href="/proj/settings.php">Back to Settings</a>
      </div>

      <div class="note">
        Tip: Settings page can link to <code>/proj/export-data.php</code> or directly to the download URLs above.
      </div>
    </div>
  </div>
</body>

</html>