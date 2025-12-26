<?php

declare(strict_types=1);

session_start();
$loggedIn = !empty($_SESSION['user_id']);
$initial = strtoupper(substr((string)($_SESSION['name'] ?? 'U'), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Discount Courses</title>
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

    .header-banner {
      background: url('back1.png') no-repeat center center;
      background-size: cover;
      padding: 40px;
      text-align: center;
      border-bottom: 2px solid #e5e5e5;
      color: black;
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

    .filter-btn {
      background: #be723f;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      padding: 10px 15px;
      cursor: pointer;
      transition: all 0.3s;
      white-space: nowrap;
    }

    .filter-btn:hover {
      background: #a55a2a;
    }

    .filter-btn.active {
      background: #2e6d3f;
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
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
      margin-top: 40px;
    }

    .card-link {
      text-decoration: none;
      color: inherit;
      display: block;
      min-width: 0;
    }

    .card {
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #ddd;
      position: relative;
      transition: all 0.3s;
      background: #fff;
      height: 100%;
      display: flex;
      flex-direction: column;
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
      line-height: 1.4;
    }

    .card-price {
      padding: 0 10px 12px;
      font-size: 14px;
      color: #444;
      margin-top: auto;
    }

    .discount-tag {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #e74c3c;
      color: white;
      font-size: 12px;
      font-weight: bold;
      padding: 3px 8px;
      border-radius: 4px;
      z-index: 2;
    }

    .empty-state {
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 20px;
      color: #777;
    }

    .empty-state h3 {
      margin: 0 0 10px;
      color: #555;
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
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="header-banner"></div>

  <div class="featured-box">
    <h2>Discount Courses for Limited Time</h2>
    <p>Explore courses available at a special price for a limited time. Take advantage of these offers to enhance your learning without breaking the bank.</p>
    <p>Not finding your favorite resource in the database? Or, did you notice some outdated information?
      <a href="mailto:support@mahorobakotoba.com" style="color:#2e6d3f; text-decoration: underline; font-weight: bold;">Contact us 📩</a>
    </p>
  </div>

  <div class="search-tabs-box">
    <input id="searchInput" type="text" placeholder="Search resources..." />

    <div style="display:flex; gap:10px; margin-top:10px; align-items:center; flex-wrap:wrap;">
      <button id="freeBtn" class="filter-btn">Free Courses</button>

      <select id="categoryFilter" class="search-tags-dropdown">
        <option value="">All Subjects</option>
      </select>

      <select id="difficultyFilter" class="search-tags-dropdown">
        <option value="">All Difficulties</option>
        <option value="Beginner">Beginner</option>
        <option value="Intermediate">Intermediate</option>
        <option value="Advanced">Advanced</option>
      </select>
    </div>
  </div>

  <div class="main-content">
    <div class="sections" id="discountGrid">
      <div class="card">
        <div class="card-title">Loading discounted courses…</div>
        <div class="card-tags">Please wait…</div>
      </div>
    </div>
  </div>

  <script>
    let allItems = [];
    let freeOnly = false;

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

    function populateCategories(items) {
      const sel = document.getElementById("categoryFilter");
      const cats = [...new Set(items.map(x => x.category).filter(Boolean))].sort();
      sel.innerHTML = `<option value="">All Subjects</option>` +
        cats.map(c => `<option value="${esc(c)}">${esc(c)}</option>`).join("");
    }

    function render(items) {
      const grid = document.getElementById("discountGrid");
      if (!items.length) {
        grid.innerHTML = `
          <div class="empty-state">
            <h3>No discounted courses found</h3>
            <p>Try changing filters or search query.</p>
          </div>
        `;
        return;
      }

      grid.innerHTML = items.map(c => {
        const img = c.image || `https://via.placeholder.com/300x160/e0e0e0/000000?text=${encodeURIComponent(c.title || "Course")}`;
        const desc = c.description ? (c.description.substring(0, 80) + "...") : "No description available";

        const badge = c.isFree ? "FREE" : `${c.discountPercent}% OFF`;
        const priceLine = c.isFree ?
          "Price: Free" :
          `Now: ৳${c.discountedPrice} (Was ৳${c.originalPrice})`;

        return `
          <a href="/proj/course.php?id=${encodeURIComponent(c.id)}" class="card-link">
            <div class="card">
              <div class="discount-tag">${esc(badge)}</div>
              <div class="card-title">${esc(c.title)}</div>
              <img src="${esc(img)}" alt="${esc(c.title)}" />
              <div class="card-tags"><div>${esc(desc)}</div></div>
              <div class="card-price">${esc(priceLine)}</div>
            </div>
          </a>
        `;
      }).join("");
    }

    function applyFilters() {
      const q = document.getElementById("searchInput").value.trim().toLowerCase();
      const cat = document.getElementById("categoryFilter").value.toLowerCase();
      const diff = document.getElementById("difficultyFilter").value.toLowerCase();

      const filtered = allItems.filter(x => {
        const okFree = !freeOnly || x.isFree === true;

        const okQ = !q ||
          (x.title && x.title.toLowerCase().includes(q)) ||
          (x.description && x.description.toLowerCase().includes(q));

        const okCat = !cat || (x.category && x.category.toLowerCase() === cat);
        const okDiff = !diff || (x.difficulty && x.difficulty.toLowerCase() === diff);

        return okFree && okQ && okCat && okDiff;
      });

      render(filtered);
    }

    async function loadDiscounts() {
      const data = await apiGet("/proj/api/discounts/list.php");
      allItems = data.courses || [];
      populateCategories(allItems);
      render(allItems);
    }

    document.getElementById("searchInput").addEventListener("input", applyFilters);
    document.getElementById("categoryFilter").addEventListener("change", applyFilters);
    document.getElementById("difficultyFilter").addEventListener("change", applyFilters);

    document.getElementById("freeBtn").addEventListener("click", () => {
      freeOnly = !freeOnly;
      document.getElementById("freeBtn").classList.toggle("active", freeOnly);
      applyFilters();
    });

    document.addEventListener("DOMContentLoaded", () => {
      loadDiscounts().catch(err => {
        alert(err.message);
        document.getElementById("discountGrid").innerHTML = `
          <div class="empty-state">
            <h3>Failed to load discounts</h3>
            <p>${esc(err.message)}</p>
          </div>
        `;
      });
    });

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        const res = await fetch("/proj/api/auth/logout.php", {
          method: "POST",
          credentials: "include"
        });
        const out = await res.json().catch(() => ({}));
        window.location.href = out.redirect || "/proj/login.html";
      });
    }
  </script>
</body>

</html>
