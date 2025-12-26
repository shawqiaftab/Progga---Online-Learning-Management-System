<?php
declare(strict_types=1);
session_start();

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /proj/login.html");
    exit;
}

require __DIR__ . '/config/db.php';

$userId = (int)$_SESSION['user_id'];
$userName = (string)($_SESSION['name'] ?? 'User');

// Fetch enrolled courses with progress
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.title,
        c.category,
        c.description,
        c.thumbnail_url,
        c.video_url,
        c.difficulty,
        c.total_lessons,
        c.duration_hours,
        c.price,
        COALESCE(cp.progress_percent, 0) AS progress_percent,
        COALESCE(cp.lessons_done, 0) AS lessons_done,
        e.enrolled_at
    FROM enrollments e
    INNER JOIN courses c ON c.id = e.course_id
    LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Courses - প্রজ্ঞা</title>
    <style>
        :root {
            --primary-color: #adca9d;
        }

        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff;
        }

        .page-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e5e5;
        }

        .page-header h1 {
            margin: 0 0 10px;
            color: #333;
            font-size: 28px;
        }

        .page-header p {
            margin: 0;
            color: #666;
            font-size: 16px;
        }

        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            flex: 1;
            min-width: 150px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .stat-item h3 {
            margin: 0 0 5px;
            font-size: 24px;
            color: var(--primary-color);
        }

        .stat-item p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .course-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
            background: #fff;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .course-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .course-content {
            padding: 15px;
        }

        .course-content h3 {
            margin: 0 0 8px;
            font-size: 18px;
            color: #333;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .progress-wrap {
            margin: 15px 0;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), #8ab87d);
            transition: width 0.3s;
        }

        .course-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .action-btn {
            flex: 1;
            padding: 8px;
            text-align: center;
            background: #e0e0e0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .action-btn.primary {
            background: var(--primary-color);
            color: white;
        }

        .action-btn.primary:hover {
            background: #8ab87d;
        }

        .action-btn:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            color: #777;
        }

        .empty-state h2 {
            margin: 0 0 15px;
            color: #555;
        }

        .empty-state p {
            margin: 0 0 20px;
            font-size: 16px;
        }

        .btn-browse {
            display: inline-block;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .btn-browse:hover {
            background: #8ab87d;
            transform: translateY(-2px);
        }

        @media (max-width: 968px) {
            .course-grid {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h1>My Enrolled Courses</h1>
            <p>Track your learning progress and continue your education journey</p>
        </div>

        <?php if (count($courses) > 0): ?>
            <div class="stats-bar">
                <div class="stat-item">
                    <h3><?= count($courses) ?></h3>
                    <p>Enrolled Courses</p>
                </div>
                <div class="stat-item">
                    <h3><?php
                        $completed = array_filter($courses, fn($c) => ((float)$c['progress_percent'] ?? 0) >= 100);
                        echo count($completed);
                    ?></h3>
                    <p>Completed</p>
                </div>
                <div class="stat-item">
                    <h3><?php
                        $totalLessons = array_sum(array_map(fn($c) => (int)($c['lessons_done'] ?? 0), $courses));
                        echo $totalLessons;
                    ?></h3>
                    <p>Lessons Completed</p>
                </div>
                <div class="stat-item">
                    <h3><?php
                        $avgProgress = count($courses) > 0 
                            ? array_sum(array_map(fn($c) => (float)($c['progress_percent'] ?? 0), $courses)) / count($courses)
                            : 0;
                        echo number_format($avgProgress, 0);
                    ?>%</h3>
                    <p>Average Progress</p>
                </div>
            </div>

            <div class="course-grid">
                <?php foreach ($courses as $course): 
                    $cid = (int)$course['id'];
                    $title = h((string)$course['title']);
                    $difficulty = h((string)($course['difficulty'] ?? 'Beginner'));
                    $lessons = (int)($course['total_lessons'] ?? 0);
                    $thumb = h((string)($course['thumbnail_url'] ?? ''));
                    $videoUrl = h((string)($course['video_url'] ?? ''));
                    $progress = (float)($course['progress_percent'] ?? 0);
                    $lessonsDone = (int)($course['lessons_done'] ?? 0);
                    $fallbackThumb = "https://via.placeholder.com/300x180/e0e0e0/000000?text=Course";
                ?>
                    <div class="course-card">
                        <img src="<?= $thumb ?: $fallbackThumb ?>" alt="<?= $title ?>">
                        <div class="course-content">
                            <h3><?= $title ?></h3>
                            <div class="course-meta">
                                <span><?= $lessons ?> Lessons</span>
                                <span><?= $difficulty ?></span>
                            </div>

                            <div class="progress-wrap">
                                <div class="progress-label">
                                    <span>Progress</span>
                                    <span><?= number_format($progress, 0) ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= number_format($progress, 0) ?>%;"></div>
                                </div>
                                <div style="font-size: 12px; color: #888; margin-top: 4px;">
                                    <?= $lessonsDone ?> of <?= $lessons ?> lessons completed
                                </div>
                            </div>

                            <div class="course-actions">
                                <a href="/proj/course.php?id=<?= $cid ?>" class="action-btn primary">
                                    <?= $progress > 0 ? 'Continue' : 'Start' ?> Learning
                                </a>
                                <a href="/proj/exam-portal.php?courseId=<?= $cid ?>" class="action-btn">Take Exam</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>You haven't enrolled in any courses yet</h2>
                <p>Start your learning journey today by enrolling in your first course!</p>
                <a href="/proj/courses.php" class="btn-browse">Browse Courses</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
