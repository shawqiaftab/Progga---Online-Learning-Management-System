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
  <title>Edit Course</title>
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

    .edit-container {
      width: 600px;
      max-width: 90%;
      margin: 40px auto;
      padding: 30px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background: #fafafa;
    }

    .edit-container h1 {
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
      min-height: 120px;
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

    .btn-save {
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

    .btn-cancel {
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

    .btn-save:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .btn-cancel:hover {
      background: #e0e0e0;
    }

    .course-image-preview {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 4px;
      margin-bottom: 10px;
    }

    .image-upload-info {
      font-size: 12px;
      color: #666;
      margin-top: 5px;
    }

    .status-msg {
      font-size: 14px;
      color: #777;
      text-align: center;
      margin-top: 10px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .edit-container {
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
        <a href="/proj/index.php" style="text-decoration: none;"><span class="logo-text">প্রজ্ঞা</span></a>
      </div>
      <ul class="nav-links">
        <li><a href="/proj/index.php#home">Home</a></li>
        <li><a href="/proj/courses.php">Courses</a></li>
        <li><a href="/proj/teacher-dashboard.php">My Dashboard</a></li>
        <li><a href="/proj/settings.php">Settings</a></li>
      </ul>
      <div class="nav-cta">
        <div class="user-avatar"><?= htmlspecialchars($initial) ?></div>
        <a href="#" class="logout-btn" id="logoutBtn">Logout</a>
      </div>
    </div>
  </nav>

  <div class="edit-container">
    <h1>Edit Course</h1>

    <form id="editCourseForm" enctype="multipart/form-data">
      <div class="form-group">
        <label>Current Thumbnail</label>
        <img id="thumbPreview" src="https://via.placeholder.com/600x400/e0e0e0/000000?text=Course" alt="Course Thumbnail" class="course-image-preview">
        <div class="image-upload-info">Recommended: 600x400px JPG/PNG</div>
        <input type="file" id="thumbnail" name="thumbnail" accept="image/*" style="margin-top: 8px;">
      </div>

      <div class="form-group">
        <label for="title">Course Title *</label>
        <input type="text" id="title" name="title" required placeholder="Course title">
      </div>

      <div class="form-group">
        <label for="category">Category *</label>
        <select id="category" name="category" required>
          <option value="">-- Select Category --</option>
          <option>Web Development</option>
          <option>Math</option>
          <option>Algorithm</option>
          <option>English</option>
          <option>Machine Learning</option>
          <option>Object Oriented Programming</option>
          <option>Data Science</option>
        </select>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" id="description" required placeholder="Describe this course..."></textarea>
      </div>

      <div class="form-group">
        <label>Difficulty Level *</label>
        <div class="difficulty-options">
          <div class="difficulty-btn" data-level="Beginner">Beginner</div>
          <div class="difficulty-btn" data-level="Intermediate">Intermediate</div>
          <div class="difficulty-btn" data-level="Advanced">Advanced</div>
        </div>
        <input type="hidden" id="difficulty" name="difficulty" value="Beginner">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Total Lessons *</label>
          <input type="number" name="lessons" id="lessons" min="1" required>
        </div>
        <div class="form-group">
          <label>Duration (Hours)</label>
          <input type="number" name="duration" id="duration" min="1" step="0.5">
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
          <input type="number" name="price" id="price" min="0" step="10" placeholder="Price in BDT">
        </div>
      </div>

      <div class="action-buttons">
        <button type="submit" class="btn-save">Save Changes</button>
        <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
      </div>

      <div id="statusMsg" class="status-msg"></div>
    </form>
  </div>

  <script>
    function getCourseId() {
      const sp = new URLSearchParams(window.location.search);
      return Number(sp.get("id") || sp.get("courseId") || 0);
    }

    async function apiGet(url) {
      const res = await fetch(url, {
        credentials: "include"
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.error || "Request failed");
      return json;
    }

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

    function setDifficulty(level) {
      document.querySelectorAll(".difficulty-btn").forEach(b => {
        b.classList.toggle("active", b.getAttribute("data-level") === level);
      });
      document.getElementById("difficulty").value = level;
    }

    function applyCourseToForm(course) {
      document.title = "Edit: " + (course.title || "Course");
      document.getElementById("title").value = course.title || "";
      document.getElementById("category").value = course.category || "";
      document.getElementById("description").value = course.description || "";
      document.getElementById("lessons").value = course.lessons || 1;
      document.getElementById("duration").value = course.duration || "";

      const diff = course.difficulty || "Beginner";
      setDifficulty(diff);

      const isFree = !!course.isFree;
      const price = isFree ? 0 : (course.price ?? 0);

      const freeToggle = document.getElementById("freeToggle");
      const priceInput = document.getElementById("priceInput");
      const priceField = document.getElementById("price");

      freeToggle.checked = isFree;
      priceInput.style.display = isFree ? "none" : "block";
      priceField.value = price;

      const img = course.thumbnailUrl || "https://via.placeholder.com/600x400/e0e0e0/000000?text=Course";
      document.getElementById("thumbPreview").src = img;
    }

    document.querySelectorAll(".difficulty-btn").forEach(btn => {
      btn.addEventListener("click", function() {
        setDifficulty(this.getAttribute("data-level"));
      });
    });

    const freeToggle = document.getElementById("freeToggle");
    const priceInput = document.getElementById("priceInput");
    const priceField = document.getElementById("price");

    freeToggle.addEventListener("change", function() {
      priceInput.style.display = this.checked ? "none" : "block";
      if (this.checked) priceField.value = "0";
    });

    document.getElementById("thumbnail").addEventListener("change", function() {
      const file = this.files[0];
      if (file) document.getElementById("thumbPreview").src = URL.createObjectURL(file);
    });

    document.getElementById("editCourseForm").addEventListener("submit", async function(e) {
      e.preventDefault();
      const status = document.getElementById("statusMsg");
      status.textContent = "Saving...";

      try {
        const courseId = getCourseId();
        if (!courseId) throw new Error("Missing course id in URL.");

        const fd = new FormData(this);
        fd.append("course_id", String(courseId));
        fd.append("is_free", freeToggle.checked ? "1" : "0");

        await apiPostForm("/proj/api/courses/update.php", fd);
        status.textContent = "Course updated successfully.";
      } catch (err) {
        status.textContent = err.message;
        alert(err.message);
      }
    });

    document.addEventListener("DOMContentLoaded", () => {
      const status = document.getElementById("statusMsg");
      const id = getCourseId();
      if (!id) {
        status.textContent = "No course id provided in URL.";
        return;
      }

      status.textContent = "Loading course...";
      apiGet("/proj/api/courses/get.php?courseId=" + encodeURIComponent(id))
        .then(data => {
          if (!data.course) throw new Error("Course not found.");
          applyCourseToForm(data.course);
          status.textContent = "";
        })
        .catch(err => {
          status.textContent = err.message;
          alert(err.message);
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