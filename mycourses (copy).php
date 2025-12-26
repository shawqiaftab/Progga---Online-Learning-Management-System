<?php

declare(strict_types=1);


require __DIR__ . "/api/auth/guard.php";

$name = (string)($_SESSION["name"] ?? "User");
$role = (string)($_SESSION["role"] ?? "learner");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Courses</title>
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
      gap: 0.75rem;
      align-items: center;
    }

    .pill {
      padding: 0.55rem 1rem;
      border-radius: 30px;
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
      font-weight: 700;
      background: transparent;
      font-size: 0.95rem;
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

    .btn-secondary:hover {
      background: var(--primary-color);
      color: white;
    }

    .header-banner {
      background: url('back1.png') no-repeat center center;
      background-size: cover;
      padding: 40px;
      text-align: center;
      border-bottom: 2px solid #e5e5e5;
      color: black;
      min-height: 60px;
    }

    .featured-box {
      margin: 40px auto;
      width: 50%;
      box-sizing: border-box;
      padding: 0;
      background: none;
      border: none;
      text-align: left;
    }

    .featured-box h2 {
      color: #333;
      margin-bottom: 15px;
    }

    .featured-box p {
      color: #555;
      line-height: 1.6;
    }

    .search-tabs-box {
      width: 50%;
      margin: 40px auto;
      padding: 20px;
      border-radius: 8px;
      border: 1px solid #ddd;
      background: #fafafa;
      box-sizing: border-box;
    }

    .search-tabs-box input[type="text"] {
      width: 100%;
      padding: 10px 12px;
      font-size: 16px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .search-tags-dropdown {
      flex: 1;
      padding: 10px 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background: #a6d1ff;
      color: white;
      font-weight: bold;
      cursor: pointer;
      box-sizing: border-box;
    }

    .main-content {
      width: 50%;
      margin: 40px auto;
    }

    .sections {
      display: flex;
      margin-top: 40px;
      gap: 20px;
      flex-wrap: wrap;
    }

    .card-link {
      text-decoration: none;
      color: inherit;
      display: block;
      flex: 1;
      min-width: 240px;
    }

    .card {
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #ddd;
      transition: all 0.3s;
      background: #fff;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border-color: var(--primary-color);
    }

    .card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }

    .card-title {
      padding: 10px;
      font-weight: bold;
      font-size: 16px;
      color: #333;
    }

    .card-tags {
      padding: 0 10px 10px;
      font-size: 14px;
      color: #666;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .featured-box,
      .search-tabs-box,
      .main-content {
        width: 90%;
      }

      .sections {
        flex-direction: column;
      }

      .card-link {
        min-width: 0;
      }
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="header-banner"></div>

  <div class="featured-box">
    <h2>My Enrolled Courses</h2>
    <p>These are the courses you've enrolled in. Track your progress, access materials, and continue learning at your own pace.</p>
    <p>
      Not finding your favorite resource in the database? Or did you notice some outdated information?
      <a href="mailto:support@mahorobakotoba.com" style="color:#2e6d3f; text-decoration: underline; font-weight: bold;">Contact us</a>
    </p>
  </div>

  <div class="search-tabs-box">
    <input id="searchInput" type="text" placeholder="Search resources..." />

    <div style="display:flex; gap:10px; margin-top:10px; align-items:center; flex-wrap:wrap;">
      <select id="subjectFilter" class="search-tags-dropdown">
        <option value="">All Subjects</option>
        <option>Math</option>
        <option>Algorithm</option>
        <option>English</option>
        <option>Machine Learning</option>
        <option>Object Oriented Programming</option>
        <option>Web Development</option>
        <option>Data Science</option>
      </select>

      <select id="difficultyFilter" class="search-tags-dropdown">
        <option value="">All Difficulties</option>
        <option>Beginner</option>
        <option>Intermediate</option>
        <option>Advanced</option>
      </select>
    </div>
  </div>

  <div class="main-content">
    <div class="sections" id="myCoursesGrid"></div>
  </div>

  <script>
    let allCourses = [];

    const API_MY_COURSES = "/proj/api/enrollments/my-courses.php";
    const API_LOGOUT = "/proj/api/auth/logout.php";

    async function apiGet(url) {
      const res = await fetch(url, {
        credentials: "include"
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.error || "Request failed");
      return json;
    }

    function esc(s) {
      return String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    function renderCourses(list) {
      const grid = document.getElementById("myCoursesGrid");
      if (!list.length) {
        grid.innerHTML = `<div style="width:100%; color:#777; padding:16px;">No enrolled courses found.</div>`;
        return;
      }

      grid.innerHTML = list.map(c => `
        <a href="/proj/course.php?id=${encodeURIComponent(c.id)}" class="card-link">
          <div class="card">
            <div class="card-title">${esc(c.title || c.category || "Course")}</div>
            <img alt="Course" src="${esc(c.thumbnailUrl || "https://via.placeholder.com/600x320/e0e0e0/000000?text=Course")}" />
            <div class="card-tags">
              <div>${esc(c.shortDescription || c.description || "")}</div>
              <div style="margin-top:6px; font-size:13px;">
                ${esc(c.category || "")} • ${esc(c.difficulty || "")}
                ${typeof c.progressPercent === "number" ? ` • ${Number(c.progressPercent)}% complete` : ""}
              </div>
            </div>
          </div>
        </a>
      `).join("");
    }

    function applyFilters() {
      const q = document.getElementById("searchInput").value.trim().toLowerCase();
      const subject = document.getElementById("subjectFilter").value;
      const diff = document.getElementById("difficultyFilter").value;

      const filtered = allCourses.filter(c => {
        const hay = `${c.title || ""} ${c.category || ""} ${c.description || ""}`.toLowerCase();
        const okQ = !q || hay.includes(q);
        const okS = !subject || (c.category === subject);
        const okD = !diff || (c.difficulty === diff);
        return okQ && okS && okD;
      });

      renderCourses(filtered);
    }

    async function boot() {
      const data = await apiGet(API_MY_COURSES);
      allCourses = data.courses || [];
      renderCourses(allCourses);

      document.getElementById("searchInput").addEventListener("input", applyFilters);
      document.getElementById("subjectFilter").addEventListener("change", applyFilters);
      document.getElementById("difficultyFilter").addEventListener("change", applyFilters);
    }

    document.getElementById("logoutBtn").addEventListener("click", async () => {
      try {
        const res = await fetch(API_LOGOUT, {
          method: "POST",
          credentials: "include"
        })
        const out = await res.json().catch(() => ({}));
        window.location.href = out.redirect || "/proj/login.html";
      } catch (e) {
        window.location.href = "/proj/login.html";
      }
    });

    document.addEventListener("DOMContentLoaded", () => {
      boot().catch(err => {
        const grid = document.getElementById("myCoursesGrid");
        grid.innerHTML = `<div style="width:100%; color:#a03030; padding:16px;">${esc(err.message)}</div>`;
      });
    });
  </script>
</body>

</html>
