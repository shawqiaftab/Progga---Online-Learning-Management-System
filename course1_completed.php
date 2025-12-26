<?php

declare(strict_types=1);

session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: /proj/login.html");
  exit;
}
$initial = strtoupper(substr((string)($_SESSION['name'] ?? 'U'), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Review Course</title>
  <style>
    :root {
      --primary-color: #adca9d;
    }

    body {
      margin: 0;
      font-family: 'Poppins', Arial, sans-serif;
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
      color: #adca9d;
      font-family: 'Georgia', serif;
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

    .nav-links a::after {
      content: '';
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

    .nav-links a:hover::after {
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
    }

    .btn-primary {
      background: var(--primary-color);
      color: white;
      box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }

    .btn-secondary {
      background: transparent;
      color: var(--primary-color);
      border: 2px solid var(--primary-color);
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #2e6d3f;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
    }

    .logout-btn {
      background: #f8d7da;
      color: #a03030;
      padding: 8px 14px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 700;
    }

    .review-container {
      width: 70%;
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .course-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .course-header img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .course-header h1 {
      font-size: 28px;
      color: #333;
      margin: 0 0 10px;
    }

    .course-meta {
      color: #666;
      font-size: 16px;
      margin-bottom: 20px;
    }

    .review-actions {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }

    .review-btn {
      padding: 10px 24px;
      background: #2e6d3f;
      color: white;
      text-decoration: none;
      font-weight: bold;
      border-radius: 6px;
      display: inline-block;
      font-size: 16px;
      transition: all 0.3s;
    }

    .review-btn:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .section-title {
      font-size: 22px;
      margin: 30px 0 20px;
      color: #333;
      padding-bottom: 8px;
      border-bottom: 1px solid #eee;
    }

    .lessons-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .lesson-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 16px;
      background: #fafafa;
      transition: all 0.3s;
    }

    .lesson-card:hover {
      border-color: #2e6d3f;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
      transform: translateY(-2px);
    }

    .lesson-card h3 {
      margin: 0 0 8px;
      font-size: 16px;
      color: #333;
    }

    .lesson-meta {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      color: #666;
    }

    .lesson-status {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
    }

    .status-completed {
      background: #d4e8d0;
      color: #2e6d3f;
    }

    .status-incomplete {
      background: #fff3cd;
      color: #856404;
    }

    .exam-section {
      background: #f0f7ef;
      border-radius: 8px;
      padding: 25px;
      text-align: center;
    }

    .exam-section h3 {
      margin: 0 0 15px;
      color: #2e6d3f;
      font-size: 20px;
    }

    .exam-section p {
      color: #555;
      margin-bottom: 20px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .retake-btn {
      background: #be723f;
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .retake-btn:hover {
      background: #a55a2a;
      transform: translateY(-2px);
    }

    .back-link {
      display: block;
      text-align: center;
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
      color: #777;
      font-size: 14px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .review-container {
        width: 90%;
      }

      .review-actions {
        flex-direction: column;
      }

      .lessons-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>

  <div class="review-container">
    <div class="course-header">
      <img id="courseImage" src="https://via.placeholder.com/800x200/e0e0e0/000000?text=Course" alt="Course Cover">
      <h1 id="courseTitle">Loading course…</h1>
      <p id="courseMeta" class="course-meta muted">Loading details…</p>
    </div>

    <div class="review-actions">
      <a href="#" id="btnReviewLessons" class="review-btn">Review All Lessons</a>
      <a href="/proj/results.php" id="btnViewResults" class="review-btn" style="background:#be723f;">View Exam Results</a>
    </div>

    <h2 class="section-title">Course Lessons</h2>
    <div class="lessons-grid" id="lessonsGrid">
      <div class="lesson-card">
        <h3 class="muted">Loading lessons…</h3>
      </div>
    </div>

    <div class="exam-section">
      <h3 id="examTitle">Final Exam</h3>
      <p id="examSummary">Loading your exam performance…</p>
      <button class="retake-btn" id="retakeBtn">Retake Final Exam</button>
    </div>

    <a href="/proj/student-dashboard.php" class="back-link">← Back to My Dashboard</a>
    <div id="statusMsg" class="muted" style="margin-top:10px; text-align:center;"></div>
  </div>

  <script>
    async function apiGet(url) {
      const res = await fetch(url, {
        credentials: "include"
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.error || "Request failed");
      return json;
    }

    function getCourseId() {
      const sp = new URLSearchParams(window.location.search);
      return Number(sp.get("courseId") || sp.get("id") || 1);
    }

    function esc(s) {
      return String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    function setLinks(courseId) {
      document.getElementById("btnViewResults").href = `/proj/results.php?courseId=${encodeURIComponent(courseId)}`;
    }

    function renderLessons(lessons) {
      const grid = document.getElementById("lessonsGrid");
      if (!lessons.length) {
        grid.innerHTML = `
          <div class="lesson-card">
            <h3>No lessons found.</h3>
            <div class="lesson-meta"><span class="muted">This course has no lessons yet.</span></div>
          </div>`;
        return;
      }

      grid.innerHTML = lessons.map(l => {
        const typeLabel = l.type === "quiz" ? "Quiz" : "Video";
        const dur = l.durationMinutes ? `${l.durationMinutes} min` : "";
        const statusClass = l.completed ? "status-completed" : "status-incomplete";
        const statusText = l.completed ? "✓ Completed" : "In progress";

        return `
          <div class="lesson-card">
            <h3>${esc(l.title)}</h3>
            <div class="lesson-meta">
              <span>${esc(typeLabel)}${dur ? " • " + esc(dur) : ""}</span>
              <span class="lesson-status ${statusClass}">${statusText}</span>
            </div>
          </div>
        `;
      }).join("");
    }

    function renderExam(exam) {
      const titleEl = document.getElementById("examTitle");
      const summaryEl = document.getElementById("examSummary");
      const statusEl = document.getElementById("statusMsg");

      if (!exam || !exam.attempted) {
        titleEl.textContent = "Final Exam";
        summaryEl.textContent = "You have not attempted the final exam yet. Take it to see your score.";
        statusEl.textContent = "No exam attempts recorded.";
        return;
      }

      titleEl.textContent = exam.name || "Final Exam";
      const score = exam.scorePercent ?? 0;
      const correct = exam.correct ?? 0;
      const total = exam.totalQuestions ?? 0;
      const date = exam.takenAt || "";

      summaryEl.textContent =
        `You scored ${score}% (${correct}/${total} correct). ` +
        (exam.message || "You can retake it anytime to improve your score.");
      statusEl.textContent = date ? `Last attempt on ${date}.` : "";
    }

    async function loadReviewPage() {
      const courseId = getCourseId();
      setLinks(courseId);

      const c = await apiGet(`/proj/api/courses/review.php?courseId=${encodeURIComponent(courseId)}`);

      const course = c.course || {};
      document.title = course.title || "Review Course";
      document.getElementById("courseTitle").textContent = course.title || "Course";
      document.getElementById("courseImage").src =
        course.image || "https://via.placeholder.com/800x200/e0e0e0/000000?text=Course";

      const metaParts = [];
      if (course.completedOn) metaParts.push(`Completed on ${course.completedOn}`);
      if (course.difficulty) metaParts.push(course.difficulty);
      if (course.lessonsCount) metaParts.push(`${course.lessonsCount} Lessons`);
      document.getElementById("courseMeta").textContent = metaParts.length ? metaParts.join(" • ") : "Course details";

      renderLessons(c.lessons || []);
      renderExam(c.exam || {});

      document.getElementById("btnReviewLessons").onclick = (e) => {
        e.preventDefault();
        window.location.href = `/proj/course.php?id=${encodeURIComponent(courseId)}`;
      };

      document.getElementById("retakeBtn").onclick = () => {
        window.location.href = `/proj/exam-portal.php?courseId=${encodeURIComponent(courseId)}`;
      };
    }

    document.addEventListener("DOMContentLoaded", () => {
      loadReviewPage().catch(err => {
        alert(err.message);
        document.getElementById("statusMsg").textContent = "Failed to load course data.";
      });
    });

    document.getElementById("logoutBtn").addEventListener("click", async (e) => {
      e.preventDefault();
      const res = await fetch("/proj/api/auth/logout.php", {
        method: "POST",
        credentials: "include"
      });
      const out = await res.json().catch(() => ({}));
      window.location.href = out.redirect || "/proj/login.html";
    });
  </script>
</body>

</html>
