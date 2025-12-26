<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /proj/login.html");
    exit;
}

require __DIR__ . '/config/db.php';

$userId = (int)$_SESSION['user_id'];
$userName = (string)($_SESSION['name'] ?? 'Student');
$userEmail = (string)($_SESSION['email'] ?? '');

// Get enrolled courses count
$enrolledCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE user_id = ?");
    $stmt->execute([$userId]);
    $enrolledCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
} catch (Throwable $e) {}

// Get completed courses (those with >90% progress)
$completedCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM course_progress WHERE user_id = ? AND progress_percent >= 90");
    $stmt->execute([$userId]);
    $completedCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
} catch (Throwable $e) {}

// Get recent enrollments with progress
$recentCourses = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            c.id, c.title, c.category, c.thumbnail_url,
            COALESCE(cp.progress_percent, 0) as progress,
            e.enrolled_at
        FROM enrollments e
        INNER JOIN courses c ON c.id = e.course_id
        LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
        WHERE e.user_id = ?
        ORDER BY e.enrolled_at DESC
        LIMIT 6
    ");
    $stmt->execute([$userId]);
    $recentCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log("Student dashboard error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - প্রজ্ঞা</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #f5f5f5;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .welcome-section h1 {
            margin: 0 0 10px 0;
            color: #2e6d3f;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #2e6d3f;
        }
        .courses-section h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .course-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .course-thumb {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #e0e0e0;
        }
        .course-info {
            padding: 15px;
        }
        .course-info h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
        }
        .course-category {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
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
            background: #2e6d3f;
            transition: width 0.3s;
        }
        .progress-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background: #2e6d3f;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #1e522a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome back, <?= htmlspecialchars($userName) ?>!</h1>
            <p>Continue your learning journey</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Enrolled Courses</h3>
                <div class="number"><?= $enrolledCount ?></div>
            </div>
            <div class="stat-card">
                <h3>Completed Courses</h3>
                <div class="number"><?= $completedCount ?></div>
            </div>
            <div class="stat-card">
                <h3>In Progress</h3>
                <div class="number"><?= max(0, $enrolledCount - $completedCount) ?></div>
            </div>
        </div>
        
        <div class="courses-section">
            <h2>My Courses</h2>
            
            <?php if (empty($recentCourses)): ?>
                <div class="empty-state">
                    <h3>No courses yet</h3>
                    <p>You haven't enrolled in any courses yet. Start learning today!</p>
                    <a href="/proj/courses.php" class="btn-primary">Browse Courses</a>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($recentCourses as $course): ?>
                        <a href="/proj/course.php?id=<?= $course['id'] ?>" class="course-card">
                            <?php if (!empty($course['thumbnail_url'])): ?>
                                <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>" alt="" class="course-thumb">
                            <?php else: ?>
                                <div class="course-thumb"></div>
                            <?php endif; ?>
                            <div class="course-info">
                                <h3><?= htmlspecialchars($course['title']) ?></h3>
                                <div class="course-category"><?= htmlspecialchars($course['category'] ?? 'General') ?></div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                                </div>
                                <div class="progress-text"><?= round($course['progress']) ?>% Complete</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

