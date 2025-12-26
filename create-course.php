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
  <title>Create Course</title>
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

    .btn-primary-nav {
      background: var(--primary-color);
      color: white;
      box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }

    .btn-secondary-nav {
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

    .create-container {
      width: 600px;
      max-width: 90%;
      margin: 40px auto;
      padding: 30px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background: #fafafa;
    }

    .create-container h1 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
      font-size: 24px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
      font-size: 14px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }

    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .difficulty-options {
      display: flex;
      gap: 12px;
      margin-top: 6px;
    }

    .difficulty-btn {
      padding: 8px 16px;
      background: #e0e0e0;
      border: 1px solid #ccc;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s;
      user-select: none;
    }

    .difficulty-btn.active {
      border-color: #2e6d3f;
      background: #eef5ed;
      color: #2e6d3f;
    }

    .price-toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 6px;
    }

    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }

    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 24px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 16px;
      width: 16px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked+.slider {
      background-color: #2e6d3f;
    }

    input:checked+.slider:before {
      transform: translateX(26px);
    }

    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 30px;
    }

    .btn-primary {
      flex: 1;
      padding: 12px;
      background: #2e6d3f;
      color: white;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-secondary {
      flex: 1;
      padding: 12px;
      background: #f0f0f0;
      color: #333;
      font-size: 16px;
      font-weight: bold;
      border: 1px solid #ccc;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .btn-secondary:hover {
      background: #e0e0e0;
    }

    .status-msg {
      margin-top: 12px;
      font-size: 14px;
      color: #666;
      text-align: center;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .create-container {
        width: 95%;
        padding: 20px;
      }

      .difficulty-options {
        flex-direction: column;
      }

      .form-row {
        flex-direction: column;
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
        <li><a href="/proj/discounts.php">Discounts</a></li>
      </ul>
      <div class="nav-cta">
        <div class="user-avatar"><?= htmlspecialchars($initial) ?></div>
        <a href="#" class="logout-btn" id="logoutBtn">Logout</a>
      </div>
    </div>
  </nav>

  <div class="create-container">
    <h1>Create New Course</h1>

    <form action="/proj/api/course/create.php" id="courseForm" enctype="multipart/form-data">
      <div class="form-group">
        <label for="title">Course Title *</label>
        <input type="text" id="title" name="title" required placeholder="e.g. Web Development Bootcamp">
      </div>

      <div class="form-group">
        <label for="category">Category *</label>
        <select id="category" name="category" required>
          <option value="">-- Select Category --</option>
          <option>Math</option>
          <option>Algorithm</option>
          <option>English</option>
          <option>Machine Learning</option>
          <option>Object Oriented Programming</option>
          <option>Web Development</option>
          <option>Data Science</option>
        </select>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" required placeholder="Describe what students will learn..."></textarea>
      </div>

      <div class="form-group">
        <label>Difficulty Level *</label>
        <div class="difficulty-options">
          <div class="difficulty-btn active" data-level="Beginner">Beginner</div>
          <div class="difficulty-btn" data-level="Intermediate">Intermediate</div>
          <div class="difficulty-btn" data-level="Advanced">Advanced</div>
        </div>
        <input type="hidden" id="difficulty" name="difficulty" value="Beginner">
      </div>

      <div class="form-group">
        <label>Course Thumbnail</label>
        <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
        <p style="font-size: 12px; color: #666; margin-top: 5px;">Recommended: 600x400px JPG/PNG</p>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Total Lessons *</label>
          <input type="number" name="lessons" min="1" value="10" required>
        </div>
        <div class="form-group">
          <label>Duration (Hours)</label>
          <input type="number" name="duration" min="1" step="0.5" value="20">
        </div>
      </div>

      <div class="form-group">
        <label>Price Settings</label>
        <div class="price-toggle">
          <span>Free Course</span>
          <label class="toggle-switch">
            <input type="checkbox" id="freeToggle">
            <span class="slider"></span>
          </label>
          <span>Paid Course</span>
        </div>
        <div id="priceInput" style="margin-top: 10px;">
          <input type="number" name="price" min="0" step="10" value="990" placeholder="Price in BDT">
        </div>
      </div>

      <div class="action-buttons">
        <button type="submit" class="btn-primary">Publish Course</button>
        <button type="button" class="btn-secondary" onclick="window.history.back()">Cancel</button>
      </div>

      <div id="statusMsg" class="status-msg"></div>
    </form>
  </div>

  <script>
    async function apiPostForm(url, formData) {
      const res = await fetch(url, {
        method: "POST",
        credentials: "include",
        body: formData
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.error || "Request failed");
      return json;
    }

    document.querySelectorAll('.difficulty-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.difficulty-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('difficulty').value = this.getAttribute('data-level');
      });
    });

    const freeToggle = document.getElementById('freeToggle');
    const priceInput = document.getElementById('priceInput');

    freeToggle.addEventListener('change', function() {
      priceInput.style.display = this.checked ? 'none' : 'block';
      if (this.checked) document.querySelector('[name="price"]').value = '0';
    });

    document.getElementById('courseForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const status = document.getElementById("statusMsg");
      status.textContent = "Publishing...";

      try {
        const fd = new FormData(this);
        fd.append("is_free", freeToggle.checked ? "1" : "0");

        const out = await apiPostForm("/proj/api/courses/create.php", fd);
        status.textContent = "Course created successfully!";
        window.location.href = "/proj/teacher-dashboard.php";
      } catch (err) {
        status.textContent = err.message;
        alert(err.message);
      }
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
