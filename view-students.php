<?php

declare(strict_types=1);

session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
  header('Location: /proj/login.html');
  exit;
}

require __DIR__ . '/config/db.php';

$teacherId = (int)$_SESSION['user_id'];
$teacherName = (string)($_SESSION['name'] ?? '');
$teacherInitial = strtoupper(mb_substr($teacherName !== '' ? $teacherName : 'T', 0, 1));

function h(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}


$stmt = $pdo->prepare("SELECT id, title, total_lessons FROM courses WHERE instructor_id = ? ORDER BY title");
$stmt->execute([$teacherId]);
$teacherCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);


$selectedCourseId = 0;
if (isset($_GET['course_id'])) {
  $selectedCourseId = (int)$_GET['course_id'];
} elseif (!empty($teacherCourses)) {
  $selectedCourseId = (int)$teacherCourses[0]['id'];
}


$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'send_message') {
  $courseId  = (int)($_POST['course_id'] ?? 0);
  $studentId = (int)($_POST['student_id'] ?? 0);
  $msg       = trim((string)($_POST['message'] ?? ''));


  $chk = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE id = ? AND instructor_id = ?");
  $chk->execute([$courseId, $teacherId]);
  $owns = ((int)$chk->fetchColumn() === 1);

  if ($owns && $studentId > 0 && $msg !== '') {
    $chk2 = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND user_id = ?");
    $chk2->execute([$courseId, $studentId]);
    $enrolled = ((int)$chk2->fetchColumn() === 1);

    if ($enrolled) {
      $ins = $pdo->prepare("
        INSERT INTO teacher_messages (teacher_id, student_id, course_id, message, created_at)
        VALUES (?, ?, ?, ?, NOW())
      ");
      $ins->execute([$teacherId, $studentId, $courseId, $msg]);
      $flash = "Message sent.";
      $selectedCourseId = $courseId;
    } else {
      $flash = "Student is not enrolled in this course.";
    }
  } else {
    $flash = "Could not send message (missing data or not allowed).";
  }
}


$selectedCourseTitle = '';
$selectedCourseLessons = 0;
foreach ($teacherCourses as $c) {
  if ((int)$c['id'] === $selectedCourseId) {
    $selectedCourseTitle = (string)$c['title'];
    $selectedCourseLessons = (int)($c['total_lessons'] ?? 0);
    break;
  }
}

function initials(string $name): string
{
  $parts = preg_split('/\s+/', trim($name)) ?: [];
  $a = $parts[0][0] ?? 'U';
  $b = $parts[1][0] ?? '';
  return strtoupper($a . $b);
}


$students = [];
if ($selectedCourseId > 0) {
  $chk = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE id = ? AND instructor_id = ?");
  $chk->execute([$selectedCourseId, $teacherId]);

  if ((int)$chk->fetchColumn() === 1) {
    $stmt = $pdo->prepare("
      SELECT
        u.id AS student_id,
        u.name,
        u.email,
        COALESCE(p.progress_percent, 0) AS progress_percent,
        COALESCE(p.lessons_done, 0) AS lessons_done
      FROM enrollments e
      JOIN users u ON u.id = e.user_id
      LEFT JOIN course_progress p
        ON p.user_id = e.user_id AND p.course_id = e.course_id
      WHERE e.course_id = ?
      ORDER BY u.name
    ");
    $stmt->execute([$selectedCourseId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Enrollments</title>
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
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
      font-size: 14px;
      transition: all 0.3s;
    }

    .logout-btn:hover {
      background: #f1b0b7;
    }

    .students-container {
      width: 80%;
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e5e5e5;
      gap: 10px;
    }

    .page-header h1 {
      color: #333;
      font-size: 24px;
      margin: 0;
    }

    .back-link {
      color: #2e6d3f;
      text-decoration: none;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: color 0.3s;
    }

    .back-link:hover {
      color: #1e522a;
    }

    .course-selector {
      margin-bottom: 25px;
    }

    .course-selector label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
    }

    .course-selector select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 16px;
      transition: border-color 0.3s;
    }

    .course-selector select:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    .students-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .student-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 16px;
      background: #fafafa;
      transition: all 0.3s;
    }

    .student-card:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .student-header {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
    }

    .student-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #adca9d;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      margin-right: 12px;
    }

    .student-info h3 {
      margin: 0 0 4px;
      font-size: 16px;
      color: #333;
    }

    .student-info p {
      margin: 0;
      font-size: 14px;
      color: #666;
    }

    .progress-bar {
      height: 6px;
      background: #e0e0e0;
      border-radius: 3px;
      margin: 12px 0;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: #2e6d3f;
      transition: width 0.3s;
    }

    .progress-text {
      font-size: 13px;
      color: #555;
      margin-bottom: 12px;
    }

    .student-actions {
      display: flex;
      gap: 10px;
    }

    .action-btn {
      flex: 1;
      padding: 6px 10px;
      font-size: 13px;
      text-align: center;
      background: #e0e0e0;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s;
    }

    .action-btn.message {
      background: #d4e8d0;
      color: #2e6d3f;
    }

    .action-btn.message:hover {
      background: #c0dcb8;
      transform: translateY(-2px);
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #777;
      border: 1px dashed #ddd;
      border-radius: 8px;
      grid-column: 1 / -1;
    }

    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }

    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .modal {
      background: white;
      width: 90%;
      max-width: 500px;
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      transform: translateY(-20px);
      transition: transform 0.3s;
    }

    .modal-overlay.active .modal {
      transform: translateY(0);
    }

    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      margin: 0;
      color: #333;
      font-size: 18px;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #999;
      transition: color 0.3s;
    }

    .close-modal:hover {
      color: #333;
    }

    .modal-body {
      padding: 20px;
    }

    .modal-body p {
      margin: 0 0 15px;
      color: #555;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #444;
    }

    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      min-height: 100px;
      font-family: Arial, sans-serif;
      resize: vertical;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }

    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    .modal-footer {
      padding: 0 20px 20px;
      display: flex;
      gap: 10px;
    }

    .btn-send {
      flex: 1;
      padding: 10px;
      background: #2e6d3f;
      color: white;
      border: none;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-cancel {
      flex: 1;
      padding: 10px;
      background: #f0f0f0;
      color: #333;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-send:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .btn-cancel:hover {
      background: #e0e0e0;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .students-container {
        width: 95%;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .students-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <nav id="navbar">
    <div class="nav-container">
      <div class="logo">
        <a href="/proj/index.php" style="text-decoration: none;">
          <span class="logo-text">প্রজ্ঞা</span>
        </a>
      </div>

      <ul class="nav-links">
        <li><a href="/proj/index.php#home">Home</a></li>
        <li><a href="/proj/courses.php">Courses</a></li>
        <li><a href="/proj/teacher-dashboard.php">My Dashboard</a></li>
        <li><a href="/proj/mycourses.php">My Courses</a></li>
      </ul>

      <div class="nav-cta">
        <div class="user-avatar"><?= h($teacherInitial) ?></div>

        <!-- logout endpoint is in /proj/api/auth/logout.php (POST). Use a form. [file:610] -->
        <form method="post" action="/proj/api/auth/logout.php" style="margin:0;">
          <button type="submit" class="logout-btn" style="border:none; cursor:pointer;">Logout</button>
        </form>
      </div>
    </div>
  </nav>

  <div class="students-container">
    <div class="page-header">
      <h1>Student Enrollments</h1>
      <a href="/proj/teacher-dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <?php if ($flash): ?>
      <p style="color:#2e6d3f; font-weight:bold;"><?= h($flash) ?></p>
    <?php endif; ?>

    <div class="course-selector">
      <label for="course">Select Course</label>
      <select id="course">
        <?php foreach ($teacherCourses as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === $selectedCourseId) ? 'selected' : '' ?>>
            <?= h((string)$c['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="students-grid">
      <?php if ($selectedCourseId <= 0): ?>
        <div class="empty-state">No courses found for this teacher.</div>
      <?php elseif (!$students): ?>
        <div class="empty-state">No students enrolled in this course yet.</div>
        <?php else: foreach ($students as $s):
          $pct = (int)max(0, min(100, round((float)$s['progress_percent'])));
          $done = (int)$s['lessons_done'];
          $total = max(0, $selectedCourseLessons);
          $progressLine = ($pct >= 100) ? "Progress: 100% (Completed)" : "Progress: {$pct}% ({$done}/{$total} lessons)";
        ?>
          <div class="student-card">
            <div class="student-header">
              <div class="student-avatar"><?= h(initials((string)$s['name'])) ?></div>
              <div class="student-info">
                <h3><?= h((string)$s['name']) ?></h3>
                <p><?= h((string)$s['email']) ?></p>
              </div>
            </div>

            <div class="progress-text"><?= h($progressLine) ?></div>
            <div class="progress-bar">
              <div class="progress-fill" style="width: <?= $pct ?>%"></div>
            </div>

            <div class="student-actions">
              <button class="action-btn message"
                data-student-id="<?= (int)$s['student_id'] ?>"
                data-student="<?= h((string)$s['name']) ?>"
                data-course="<?= h($selectedCourseTitle) ?>"
                data-course-id="<?= (int)$selectedCourseId ?>">
                Message
              </button>
            </div>
          </div>
      <?php endforeach;
      endif; ?>
    </div>
  </div>

  <!-- Messaging -->
  <div class="modal-overlay" id="messageModal">
    <div class="modal">
      <div class="modal-header">
        <h2>Send Message to <span id="modalStudentName">Student</span></h2>
        <button class="close-modal" type="button">&times;</button>
      </div>

      <form method="post" id="messageForm">
        <input type="hidden" name="action" value="send_message">
        <input type="hidden" name="student_id" id="studentIdInput" value="">
        <input type="hidden" name="course_id" id="courseIdInput" value="">

        <div class="modal-body">
          <p>Course: <strong id="modalCourseName">Course Name</strong></p>
          <div class="form-group">
            <label for="messageText">Your Message</label>
            <textarea id="messageText" name="message" placeholder="Type your message here..." required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" type="button">Cancel</button>
          <button class="btn-send" type="submit">Send Message</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // course filter
    const courseSelect = document.getElementById('course');
    courseSelect.addEventListener('change', () => {
      const id = courseSelect.value;
      window.location = `/proj/view-students.php?course_id=${encodeURIComponent(id)}`;
    });

    // modal logic
    const modal = document.getElementById('messageModal');
    const studentNameSpan = document.getElementById('modalStudentName');
    const courseNameSpan = document.getElementById('modalCourseName');
    const messageTextarea = document.getElementById('messageText');
    const closeButtons = document.querySelectorAll('.close-modal, .btn-cancel');

    const studentIdInput = document.getElementById('studentIdInput');
    const courseIdInput = document.getElementById('courseIdInput');

    document.querySelectorAll('.action-btn.message').forEach(btn => {
      btn.addEventListener('click', function() {
        studentNameSpan.textContent = this.getAttribute('data-student');
        courseNameSpan.textContent = this.getAttribute('data-course');
        studentIdInput.value = this.getAttribute('data-student-id');
        courseIdInput.value = this.getAttribute('data-course-id');
        messageTextarea.value = '';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
    });

    closeButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      });
    });

    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  </script>
</body>

</html>