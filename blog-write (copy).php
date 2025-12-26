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
  <title>Write Blog</title>
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

    .editor-container {
      width: 70%;
      max-width: 900px;
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

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
      font-size: 16px;
    }

    .form-group input {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    .form-group textarea {
      width: 100%;
      min-height: 300px;
      padding: 15px;
      font-size: 16px;
      line-height: 1.6;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
      resize: vertical;
      transition: border-color 0.3s;
    }

    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    .meta-row {
      display: flex;
      gap: 15px;
      margin-bottom: 25px;
    }

    .meta-group {
      flex: 1;
    }

    .meta-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
      font-size: 14px;
    }

    .meta-group select,
    .meta-group input {
      width: 100%;
      padding: 8px 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: border-color 0.3s;
    }

    .meta-group select:focus,
    .meta-group input:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 20px;
    }

    .btn-save {
      background: #2e6d3f;
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-save:hover {
      background: #1e522a;
      transform: translateY(-2px);
    }

    .btn-draft {
      background: #f0f0f0;
      color: #333;
      padding: 10px 24px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-draft:hover {
      background: #e0e0e0;
      transform: translateY(-2px);
    }

    .publish-info {
      background: #eef5ed;
      border-left: 4px solid #2e6d3f;
      padding: 12px;
      border-radius: 0 6px 6px 0;
      font-size: 14px;
      color: #555;
      margin-top: 15px;
    }

    .helper-bar {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 10px;
      color: #666;
      font-size: 13px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .editor-container {
        width: 90%;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .meta-row {
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
        <li><a href="/proj/mycourses.php">My Courses</a></li>
        <li><a href="/proj/blog-dashboard.php">My Blog</a></li>
        <li><a href="/proj/discounts.php">Discounts</a></li>
      </ul>

      <div class="nav-cta">
        <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1))) ?></div>
        <a href="#" class="logout-btn" id="logoutBtn">Logout</a>
      </div>
    </div>
  </nav>

  <div class="editor-container">
    <div class="page-header">
      <h1 id="pageTitle">Write a New Blog Post</h1>
      <a href="/proj/blog-dashboard.php" class="back-link">← Back to Blog</a>
    </div>

    <div class="form-group">
      <label for="postTitle">Post Title *</label>
      <input type="text" id="postTitle" placeholder="e.g. How I Built My First Portfolio Website">
    </div>

    <div class="meta-row">
      <div class="meta-group">
        <label for="postCategory">Category</label>
        <select id="postCategory">
          <option>Learning Journey</option>
          <option>Course Tips</option>
          <option>Project Showcase</option>
          <option>Study Strategies</option>
          <option>Web Development</option>
          <option>Algorithms</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="postContent">Content *</label>
      <textarea id="postContent" placeholder="Start writing your blog post here..."></textarea>
      <div class="helper-bar">
        <span id="statusText">Not saved yet</span>
        <span id="wordCount">0 words</span>
      </div>
    </div>

    <div class="publish-info">
      💡 Posts are automatically saved as drafts. Click "Publish" when ready to share with the community!
    </div>

    <div class="action-buttons">
      <button class="btn-draft" id="btnSaveDraft">Save as Draft</button>
      <button class="btn-save" id="btnPublish">Publish Post</button>
    </div>
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

    function getQueryParam(name) {
      const u = new URL(window.location.href);
      return u.searchParams.get(name);
    }

    function wordCount(text) {
      const t = String(text || "").trim();
      if (!t) return 0;
      return t.split(/\s+/).length;
    }

    function setStatus(msg) {
      document.getElementById("statusText").textContent = msg;
    }

    function updateWordCount() {
      document.getElementById("wordCount").textContent =
        `${wordCount(document.getElementById("postContent").value)} words`;
    }

    function getPayload(statusOverride) {
      return {
        id: getQueryParam("id"), 
        title: document.getElementById("postTitle").value.trim(),
        category: document.getElementById("postCategory").value,
        status: statusOverride || "draft",
        content: document.getElementById("postContent").value
      };
    }

    function validateMin() {
      const title = document.getElementById("postTitle").value.trim();
      const content = document.getElementById("postContent").value.trim();
      if (!title) throw new Error("Please enter a post title.");
      if (!content) throw new Error("Please write some content.");
    }

    async function loadForEditIfNeeded() {
      const id = getQueryParam("id");
      if (!id) return;

      document.getElementById("pageTitle").textContent = "Edit Blog Post";
      setStatus("Loading...");

      const data = await apiGet(`/proj/api/blog/get.php?id=${encodeURIComponent(id)}`);
      document.getElementById("postTitle").value = data.post.title || "";
      document.getElementById("postCategory").value = data.post.category || "Learning Journey";
      document.getElementById("postContent").value = data.post.content || "";

      updateWordCount();
      setStatus("Loaded");
    }

    async function save(statusOverride) {
      validateMin();
      setStatus("Saving...");

      const payload = getPayload(statusOverride);
      const out = await apiPost("/proj/api/blog/save.php", payload);

      if (!getQueryParam("id")) {
        const url = new URL(window.location.href);
        url.searchParams.set("id", out.id);
        history.replaceState(null, "", url.toString());
      }

      updateWordCount();
      setStatus(out.status === "published" ? "Published" : "Draft saved");
      return out;
    }

    document.getElementById("postContent").addEventListener("input", updateWordCount);

    document.getElementById("btnSaveDraft").addEventListener("click", async () => {
      try {
        await save("draft");
      } catch (err) {
        alert(err.message);
        setStatus("Not saved");
      }
    });

    document.getElementById("btnPublish").addEventListener("click", async () => {
      try {
        await save("published");
        window.location.href = "/proj/blog-dashboard.php";
      } catch (err) {
        alert(err.message);
        setStatus("Not saved");
      }
    });

    document.addEventListener("DOMContentLoaded", () => {
      updateWordCount();
      loadForEditIfNeeded().catch(err => {
        alert(err.message);
        setStatus("Failed to load");
      });
    });

    // logout
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