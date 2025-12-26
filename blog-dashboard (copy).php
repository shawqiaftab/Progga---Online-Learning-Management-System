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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Blog</title>
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

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
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

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #2e6d3f;
      color: #fff;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logout-btn {
      background: #f8d7da;
      color: #a03030;
      padding: 8px 14px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 700;
    }

    .blog-container {
      width: 80%;
      max-width: 1100px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e5e5e5;
    }

    .dashboard-header h1 {
      color: #333;
      font-size: 24px;
      margin: 0;
    }

    .new-post-btn {
      background: #2e6d3f;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 6px;
      display: inline-block;
      transition: all 0.3s;
    }

    .new-post-btn:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      transition: all 0.3s;
    }

    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-card h3 {
      margin: 0 0 10px;
      color: #666;
      font-size: 14px;
    }

    .stat-card p {
      margin: 0;
      font-size: 22px;
      font-weight: bold;
      color: #2e6d3f;
    }

    .section-title {
      font-size: 20px;
      margin: 30px 0 20px;
      color: #333;
      padding-bottom: 8px;
      border-bottom: 1px solid #eee;
    }

    .post-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 25px;
    }

    .post-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      background: #fafafa;
      transition: all 0.3s;
    }

    .post-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border-color: var(--primary-color);
    }

    .post-content {
      padding: 20px;
    }

    .post-status {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .status-draft {
      background: #fff3cd;
      color: #856404;
    }

    .status-published {
      background: #d4e8d0;
      color: #2e6d3f;
    }

    .post-card h3 {
      margin: 0 0 10px;
      font-size: 18px;
      color: #333;
    }

    .post-excerpt {
      color: #555;
      font-size: 14px;
      margin-bottom: 15px;
      line-height: 1.5;
    }

    .post-meta {
      display: flex;
      justify-content: space-between;
      color: #777;
      font-size: 13px;
      border-top: 1px solid #eee;
      padding-top: 12px;
    }

    .post-actions {
      display: flex;
      gap: 10px;
      margin-top: 12px;
      flex-wrap: wrap;
    }

    .action-btn {
      padding: 6px 12px;
      font-size: 13px;
      background: #e0e0e0;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s;
    }

    .action-btn:hover {
      transform: translateY(-2px);
    }

    .action-btn.edit {
      background: #d4e8d0;
      color: #2e6d3f;
    }

    .action-btn.edit:hover {
      background: #c0dcb8;
    }

    .action-btn.delete {
      background: #f8d7da;
      color: #a03030;
    }

    .action-btn.delete:hover {
      background: #f0bfbf;
    }

    .empty-state {
      text-align: center;
      padding: 50px;
      color: #777;
      grid-column: 1 / -1;
    }

    .empty-state h3 {
      margin: 0 0 15px;
      color: #555;
    }

    .empty-state p {
      max-width: 500px;
      margin: 0 auto 20px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .blog-container {
        width: 95%;
      }

      .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .post-grid {
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
        <li><a href="/proj/index.php#features">Features</a></li>
        <li><a href="/proj/index.php#testimonials">Reviews</a></li>
        <li><a href="/proj/blog-dashboard.php">My Blog</a></li>
      </ul>

      <div class="nav-cta">
        <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1))) ?></div>
        <a href="#" class="logout-btn" id="logoutBtn">Logout</a>
      </div>
    </div>
  </nav>

  <div class="blog-container">
    <div class="dashboard-header">
      <h1>My Blog Posts</h1>
      <a href="/proj/blog-write.php" class="new-post-btn">+ New Post</a>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>TOTAL POSTS</h3>
        <p id="statTotal">0</p>
      </div>
      <div class="stat-card">
        <h3>PUBLISHED</h3>
        <p id="statPublished">0</p>
      </div>
      <div class="stat-card">
        <h3>DRAFTS</h3>
        <p id="statDrafts">0</p>
      </div>
      <div class="stat-card">
        <h3>TOTAL VIEWS</h3>
        <p id="statViews">0</p>
      </div>
    </div>

    <h2 class="section-title">Published Posts</h2>
    <div class="post-grid" id="publishedGrid"></div>

    <h2 class="section-title">Drafts</h2>
    <div class="post-grid" id="draftGrid"></div>
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

    async function apiPost(url, data) {
      const res = await fetch(url, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
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

    function renderStats(stats) {
      document.getElementById("statTotal").textContent = stats.totalPosts ?? 0;
      document.getElementById("statPublished").textContent = stats.published ?? 0;
      document.getElementById("statDrafts").textContent = stats.drafts ?? 0;
      document.getElementById("statViews").textContent = (stats.totalViews ?? 0).toLocaleString();
    }

    function postCardHtml(p) {
      const statusClass = p.status === "published" ? "status-published" : "status-draft";
      const statusLabel = p.status === "published" ? "Published" : "Draft";

      const dateText = p.status === "published" ?
        (p.createdAt ?? "") :
        ("Last edited: " + (p.updatedAt ?? (p.createdAt ?? "")));

      const viewsText = p.status === "published" ? `${p.views ?? 0} views` : "";

      const primaryBtnText = p.status === "published" ? "Edit" : "Continue";
      const showDelete = p.status !== "published";

      return `
        <div class="post-card" data-post-id="${esc(p.id)}" data-status="${esc(p.status)}">
          <div class="post-content">
            <span class="post-status ${statusClass}">${statusLabel}</span>
            <h3>${esc(p.title)}</h3>
            <p class="post-excerpt">${esc(p.excerpt)}</p>
            <div class="post-meta">
              <span>${esc(dateText)}</span>
              <span>${esc(viewsText)}</span>
            </div>
            <div class="post-actions">
              <button class="action-btn edit">${primaryBtnText}</button>
              <button class="action-btn view">View</button>
              ${showDelete ? `<button class="action-btn delete">Delete</button>` : ``}
            </div>
          </div>
        </div>
      `;
    }

    function mountPosts(published, drafts) {
      const pubWrap = document.getElementById("publishedGrid");
      const draftWrap = document.getElementById("draftGrid");

      pubWrap.innerHTML = published.length ?
        published.map(postCardHtml).join("") :
        `<div class="empty-state"><h3>No published posts yet</h3><p>Publish your first post from the editor.</p></div>`;

      draftWrap.innerHTML = drafts.length ?
        drafts.map(postCardHtml).join("") :
        `<div class="empty-state"><h3>No drafts</h3><p>Your drafts will appear here.</p></div>`;
    }

    async function loadBlogDashboard() {
      const data = await apiGet("/proj/api/blog/list.php");
      renderStats(data.stats || {});
      mountPosts(data.published || [], data.drafts || []);
    }

    document.addEventListener("click", async (e) => {
      const card = e.target.closest(".post-card");
      if (!card) return;

      const postId = card.getAttribute("data-post-id");

      if (e.target.classList.contains("edit")) {
        window.location.href = `/proj/blog-write.php?id=${encodeURIComponent(postId)}`;
      }

      if (e.target.classList.contains("view")) {
        window.location.href = `/proj/blog-view.php?id=${encodeURIComponent(postId)}`;
      }

      if (e.target.classList.contains("delete")) {
        if (!confirm("Are you sure you want to delete this draft?")) return;
        try {
          await apiPost("/proj/api/blog/delete.php", {
            id: postId
          });
          card.remove();
        } catch (err) {
          alert(err.message);
        }
      }
    });

    document.addEventListener("DOMContentLoaded", () => {
      loadBlogDashboard().catch(err => alert(err.message));
    });

    // logout
    document.getElementById("logoutBtn").addEventListener("click", async (e) => {
      e.preventDefault();
      const out = await apiPost("/proj/api/auth/logout.php", {});
      window.location.href = out.redirect || "/proj/login.html";
    });
  </script>
</body>

</html>