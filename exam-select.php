<?php
declare(strict_types=1);
session_start();

$loggedIn = !empty($_SESSION['user_id']);
$initial = strtoupper(substr((string)($_SESSION['name'] ?? 'U'), 0, 1));

$courseId = isset($_GET['courseId']) ? (int)$_GET['course_id'] : 0;
if ($courseId <= 0) {
  header("Location: /proj/courses.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Select Difficulty</title>

  <style>
    :root { --primary-color: #adca9d; }

    body{
      margin:0;
      font-family:Poppins, Arial, sans-serif;
      background:#ffffff;
      color:#333;
    }

   
    nav{
      background: rgba(245, 240, 232, 0.98);
      backdrop-filter: blur(10px);
      padding: 1.2rem 5%;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 20px rgba(255, 107, 53, 0.1);
      border-bottom: 2px solid rgba(255, 107, 53, 0.1);
    }
    .nav-container{
      display:flex;
      justify-content:space-between;
      align-items:center;
      max-width:1400px;
      margin:0 auto;
    }
    .logo{ display:flex; align-items:center; gap:.5rem; }
    .logo-text{
      font-size:2rem;
      font-weight:700;
      color: var(--primary-color);
      font-family: Georgia, serif;
    }
    .nav-links{
      display:flex;
      list-style:none;
      gap:2.5rem;
      align-items:center;
      margin:0;
      padding:0;
    }
    .nav-links a{
      text-decoration:none;
      color:#2d3436;
      font-weight:500;
      transition: color .3s;
      position:relative;
      font-size:1rem;
    }
    .nav-links a::after{
      content:"";
      position:absolute;
      bottom:-5px;
      left:0;
      width:0;
      height:3px;
      background:#FF6B35;
      transition: width .3s;
      border-radius:2px;
    }
    .nav-links a:hover{ color:#FF6B35; }
    .nav-links a:hover::after{ width:100%; }

    .nav-cta{ display:flex; gap:1rem; align-items:center; }
    .btn{
      padding:.8rem 1.8rem;
      border-radius:30px;
      text-decoration:none;
      font-weight:600;
      transition: all .3s;
      border:none;
      cursor:pointer;
      font-size:.95rem;
      display:inline-block;
    }
    .btn-primary{
      background: var(--primary-color);
      color:white;
      box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }
    .btn-secondary{
      background: transparent;
      color: var(--primary-color);
      border: 2px solid var(--primary-color);
    }
    .btn-secondary:hover{ background: var(--primary-color); color:white; }
    .user-avatar{
      width:36px; height:36px;
      border-radius:50%;
      background:#2e6d3f;
      display:flex;
      align-items:center;
      justify-content:center;
      color:white;
      font-weight:bold;
    }
    .logout-btn{
      background:#f8d7da;
      color:#a03030;
      padding: 8px 14px;
      border-radius:20px;
      text-decoration:none;
      font-weight:700;
    }

   
    .course-container{
      width:50%;
      margin:40px auto;
    }
    @media (max-width: 968px){
      .nav-links{ display:none; }
      .course-container{ width:90%; }
    }

    .page-title{
      font-size:26px;
      margin-bottom:12px;
      color:#333;
    }
    .page-sub{
      color:#666;
      font-size:14px;
      margin-bottom:20px;
      border-bottom:1px solid #eee;
      padding-bottom:15px;
      line-height:1.6;
    }

    
    .difficulty-grid{
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap:12px;
      margin-top:20px;
    }
    @media (max-width: 968px){
      .difficulty-grid{ grid-template-columns: 1fr; }
    }

    .diff-card{
      text-decoration:none;
      color:#333;
      background:#fafafa;
      border:1px solid #ddd;
      border-radius:12px;
      padding:18px 16px;
      transition: all .3s;
      display:block;
    }
    .diff-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border-color:#2e6d3f;
    }
    .diff-badge{
      display:inline-block;
      font-size:12px;
      font-weight:700;
      padding:6px 10px;
      border-radius:999px;
      background:#eef5ed;
      color:#2e6d3f;
      margin-bottom:10px;
    }
    .diff-title{
      margin:0 0 6px;
      font-size:18px;
      color:#2e6d3f;
    }
    .diff-desc{
      margin:0;
      font-size:14px;
      color:#555;
      line-height:1.5;
    }

    .action-row{
      display:flex;
      gap:12px;
      margin-top:22px;
      padding-top:20px;
      border-top:1px solid #eee;
      flex-wrap:wrap;
    }
    .action-btn{
      padding: 8px 16px;
      background:#f0f0f0;
      color:#333;
      text-decoration:none;
      font-size:14px;
      border-radius:4px;
      transition: all .3s;
      border:none;
      cursor:pointer;
      display:inline-block;
    }
    .action-btn:hover{ background:#e0e0e0; transform: translateY(-2px); }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="course-container">
    <h1 class="page-title">Select difficulty</h1>
    <div class="page-sub">
      Choose a difficulty level for this course before starting the test.
    </div>

    <div class="difficulty-grid">
      <a class="diff-card" href="/proj/exam-portal.php?courseId=<?=urlencode((string)$courseId)?>&difficulty=easy">
        <span class="diff-badge">Recommended</span>
        <h3 class="diff-title">Easy</h3>
        <p class="diff-desc">Basic questions to warm up.</p>
      </a>

      <a class="diff-card" href="/proj/exam-portal.php?courseId=<?=urlencode((string)$courseId)?>&difficulty=medium">
        <span class="diff-badge">Standard</span>
        <h3 class="diff-title">Medium</h3>
        <p class="diff-desc">Standard difficulty for practice.</p>
      </a>

      <a class="diff-card" href="/proj/exam-portal.php?courseId=<?=urlencode((string)$courseId)?>&difficulty=hard">
        <span class="diff-badge">Challenge</span>
        <h3 class="diff-title">Hard</h3>
        <p class="diff-desc">Challenging questions to test mastery.</p>
      </a>
    </div>

    <div class="action-row">
      <a class="action-btn" href="/proj/course.php?id=<?=urlencode((string)$courseId)?>">Back to Course</a>
    </div>
  </div>
</body>
</html>
