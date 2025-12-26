<?php

declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
  header("Location: /proj/login.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Exam Portal</title>

  <style>
    :root {
      --primary-color: #adca9d;
    }

    body {
      margin: 0;
      font-family: Poppins, Arial, sans-serif;
      background: #ffffff;
    }

    nav {
      background: rgba(245, 240, 232, 0.98);
      backdrop-filter: blur(10px);
      padding: 1.2rem 5%;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 20px rgba(255, 107, 53, 0.1);
      border-bottom: 2px solid rgba(255, 107, 53, 0.1);
    }

    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1400px;
      margin: 0 auto;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .logo-text {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-color);
      font-family: Georgia, serif;
    }

    .nav-links {
      display: flex;
      list-style: none;
      gap: 2.5rem;
      align-items: center;
      margin: 0;
      padding: 0;
    }

    .nav-links a {
      text-decoration: none;
      color: #2d3436;
      font-weight: 500;
      transition: color 0.3s;
      position: relative;
      font-size: 1rem;
    }

    .nav-links a:after {
      content: "";
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 3px;
      background: #FF6B35;
      transition: width 0.3s;
      border-radius: 2px;
    }

    .nav-links a:hover {
      color: #FF6B35;
    }

    .nav-links a:hover:after {
      width: 100%;
    }

    .nav-cta {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .btn {
      padding: 0.8rem 1.8rem;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
      display: inline-block;
    }

    .btn-secondary {
      background: transparent;
      color: var(--primary-color);
      border: 2px solid var(--primary-color);
    }

    .btn-secondary:hover {
      background: var(--primary-color);
      color: white;
    }

    .exam-container {
      width: 70%;
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .intro-screen {
      background: #fafafa;
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 40px;
      text-align: center;
    }

    .intro-screen h1 {
      font-size: 28px;
      color: #333;
      margin: 0 0 20px;
    }

    .exam-details {
      margin: 25px 0;
      font-size: 16px;
      line-height: 1.6;
      color: #555;
    }

    .detail-item {
      margin: 12px 0;
    }

    .detail-label {
      font-weight: bold;
      color: #2e6d3f;
    }

    .instructions {
      background: #eef5ed;
      padding: 20px;
      border-radius: 8px;
      margin: 25px 0;
      text-align: left;
    }

    .instructions h2 {
      margin: 0 0 15px;
      color: #2e6d3f;
      font-size: 18px;
    }

    .instructions ul {
      margin: 0;
      padding-left: 20px;
      color: #555;
    }

    .start-btn {
      padding: 14px 32px;
      background: #2e6d3f;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 20px;
      transition: all 0.3s;
    }

    .start-btn:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .back-link {
      display: block;
      margin-top: 20px;
      color: #2e6d3f;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
    }

    .back-link:hover {
      color: #1e522a;
      text-decoration: underline;
    }

    .muted {
      margin-top: 12px;
      color: #777;
      font-size: 14px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .exam-container {
        width: 95%;
      }

      .intro-screen {
        padding: 20px;
      }
    }
  </style>
</head>

<body>
  <nav id="navbar">
    <div class="nav-container">
      <div class="logo">
        <a href="/proj/index.php" style="text-decoration:none;">
          <span class="logo-text">প্রজ্ঞা</span>
        </a>
      </div>

      <ul class="nav-links">
        <li><a href="/proj/index.php">Home</a></li>
        <li><a href="/proj/courses.php">Courses</a></li>
        <li><a href="/proj/student-dashboard.php">My Dashboard</a></li>
        <li><a href="/proj/discounts.php">Discounts</a></li>
      </ul>

      <div class="nav-cta">
        <a href="/proj/api/auth/logout.php" class="btn btn-secondary">Logout</a>
      </div>
    </div>
  </nav>

  <div class="exam-container">
    <div class="intro-screen" id="introScreen">
      <h1 id="examTitle">Loading exam…</h1>

      <div class="exam-details">
        <div class="detail-item"><span class="detail-label">Difficulty:</span> <span id="difficultyText">—</span></div>
        <div class="detail-item"><span class="detail-label">Total Questions:</span> <span id="totalQuestions">—</span></div>
        <div class="detail-item"><span class="detail-label">Time Limit:</span> <span id="timeLimit">—</span></div>
        <div class="detail-item"><span class="detail-label">Passing Score:</span> <span id="passingScore">—</span></div>
        <div class="detail-item"><span class="detail-label">Attempts Allowed:</span> <span id="attemptsAllowed">—</span></div>
        <div class="detail-item"><span class="detail-label">Attempts Used:</span> <span id="attemptsUsed">—</span></div>
      </div>

      <div class="instructions">
        <h2>Exam Instructions</h2>
        <ul>
          <li>Read each question carefully before answering.</li>
          <li>You can navigate between questions using "Previous" and "Next".</li>
          <li>Your answers are saved automatically.</li>
          <li>Good luck!</li>
        </ul>
      </div>

      <button class="start-btn" id="startExamBtn">Start Exam</button>
      <a id="backToCourseLink" href="/proj/courses.php" class="back-link">← Back to Course</a>

      <div id="statusMsg" class="muted"></div>
    </div>
  </div>

  <script>
    let courseId = 0;
    let difficulty = "easy";




    function getCourseId() {
      const sp = new URLSearchParams(window.location.search);
      return Number(sp.get("courseId") || sp.get("id") || 0);
    }

    function getDifficulty() {
      const sp = new URLSearchParams(window.location.search);
      return String(sp.get("difficulty") || "easy").toLowerCase();
    }

    async function apiGet(url) {
      const res = await fetch(url, {
        credentials: "include"
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.error || "Request failed");
      return json;
    }

    async function apiPostJson(url, body) {
      const res = await fetch(url, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(body)
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.error || "Request failed");
      return json;
    }

    function setIntro(data) {
      const exam = data.exam || {};
      const qCount = Array.isArray(exam.questions) ? exam.questions.length : "—";

      document.getElementById("examTitle").textContent = exam.title || "Final Exam";
      document.getElementById("difficultyText").textContent = difficulty.toUpperCase();
      document.getElementById("totalQuestions").textContent = qCount;

      const dur = (exam.durationMinutes ?? "—");
      document.getElementById("timeLimit").textContent = `${dur} minutes`;

      document.getElementById("passingScore").textContent = (exam.passingScore ?? "—");
      document.getElementById("attemptsAllowed").textContent = (exam.attemptsAllowed ?? "—");
      document.getElementById("attemptsUsed").textContent = (data.attemptsUsed ?? 0);

      document.getElementById("backToCourseLink").href =
        `/proj/course.php?id=${encodeURIComponent(courseId)}`;
    }

    async function boot() {
      courseId = getCourseId();
      difficulty = getDifficulty();

      const status = document.getElementById("statusMsg");

      // if (!courseId) {
      //   status.textContent = "Missing courseId in URL. Open via exam-select or add ?courseId=...";
      //   document.getElementById("startExamBtn").disabled = true;
      //   return;
      // }


      const data = await apiGet(
        `/proj/api/exams/get.php?courseId=${encodeURIComponent(courseId)}&difficulty=${encodeURIComponent(difficulty)}`
      );
      setIntro(data);

      document.getElementById("startExamBtn").addEventListener("click", async () => {
        try {
          status.textContent = "Starting exam...";

          const started = await apiPostJson("/proj/api/exams/start.php", {
            courseId,
            difficulty
          });
          try {
            sessionStorage.setItem(
              `attempt_${started.attemptId}_answers`,
              JSON.stringify(started.answers || {})
            );
          } catch {}


          window.location.href =
            `/proj/exam.php?courseId=${encodeURIComponent(courseId)}` +
            `&difficulty=${encodeURIComponent(difficulty)}` +
            `&attemptId=${encodeURIComponent(started.attemptId)}` +
            `&endsAt=${encodeURIComponent(started.endsAt)}`;
        } catch (err) {
          status.textContent = err.message || "Failed to start exam.";
          alert(status.textContent);
        }
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      boot().catch(err => {
        document.getElementById("statusMsg").textContent = err.message || "Failed to load exam portal.";
        alert(err.message || "Failed to load exam portal.");
      });
    });
  </script>
</body>

</html>