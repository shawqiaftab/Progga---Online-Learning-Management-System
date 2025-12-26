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
  <title>Earnings</title>
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

    .earnings-container {
      width: 80%;
      max-width: 1100px;
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

    .summary-stats {
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

    .stat-card .amount {
      font-size: 22px;
      font-weight: bold;
      color: #2e6d3f;
    }

    .stat-card .period {
      font-size: 12px;
      color: #888;
      margin-top: 5px;
    }

    .chart-placeholder {
      background: #f5f9f4;
      border: 1px solid #ddd;
      border-radius: 8px;
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 30px;
      color: #666;
      font-style: italic;
    }

    .section-title {
      font-size: 20px;
      margin: 30px 0 15px;
      color: #333;
    }

    .earnings-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
    }

    .earnings-table th {
      background: #eef5ed;
      padding: 12px 15px;
      text-align: left;
      font-weight: bold;
      color: #2e6d3f;
      border-bottom: 1px solid #d0e0cd;
    }

    .earnings-table td {
      padding: 12px 15px;
      border-bottom: 1px solid #eee;
      color: #444;
    }

    .earnings-table tr:last-child td {
      border-bottom: none;
    }

    .earnings-table tbody tr:hover {
      background: #f9f9f9;
    }

    .course-title {
      font-weight: bold;
      color: #333;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }

    .status-paid {
      background: #d4e8d0;
      color: #2e6d3f;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
    }

    .payout-info {
      background: #f0f7ef;
      border-left: 4px solid #2e6d3f;
      padding: 15px;
      margin-top: 25px;
      border-radius: 0 6px 6px 0;
    }

    .payout-info h3 {
      margin: 0 0 8px;
      color: #2e6d3f;
    }

    .payout-info p {
      margin: 0;
      font-size: 14px;
      color: #555;
      line-height: 1.6;
    }

    .muted {
      color: #777;
      font-size: 14px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .earnings-container {
        width: 95%;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .earnings-table {
        font-size: 14px;
      }

      .earnings-table th,
      .earnings-table td {
        padding: 8px 10px;
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
        <li><a href="/proj/discounts.php">Discounts</a></li>
      </ul>
      <div class="nav-cta">
        <div class="user-avatar"><?= htmlspecialchars($initial) ?></div>
        <a href="#" class="logout-btn" id="logoutBtn">Logout</a>
      </div>
    </div>
  </nav>

  <div class="earnings-container">
    <div class="page-header">
      <h1>Earnings Dashboard</h1>
      <a href="/proj/teacher-dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <div class="summary-stats">
      <div class="stat-card">
        <h3>TOTAL EARNINGS</h3>
        <div class="amount" id="totalEarnings">৳ 0</div>
        <div class="period" id="totalEarningsPeriod">All time</div>
      </div>
      <div class="stat-card">
        <h3>THIS MONTH</h3>
        <div class="amount" id="monthEarnings">৳ 0</div>
        <div class="period" id="monthPeriod">—</div>
      </div>
      <div class="stat-card">
        <h3>PENDING PAYOUT</h3>
        <div class="amount" id="pendingPayout">৳ 0</div>
        <div class="period" id="nextPayout">—</div>
      </div>
      <div class="stat-card">
        <h3>TOTAL SALES</h3>
        <div class="amount" id="totalSales">0</div>
        <div class="period">Courses sold</div>
      </div>
    </div>

    <div class="chart-placeholder" id="chartPlaceholder">
      Earnings chart will appear here (integrates with your analytics backend)
    </div>

    <h2 class="section-title">Earnings by Course</h2>
    <table class="earnings-table">
      <thead>
        <tr>
          <th>Course</th>
          <th>Students</th>
          <th>Total Earned</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="earningsBody">
        <tr>
          <td colspan="4" class="muted">Loading…</td>
        </tr>
      </tbody>
    </table>

    <div class="payout-info">
      <h3>💡 Payout Information</h3>
      <p>Earnings are paid out monthly on the 5th. Minimum payout threshold: ৳ 500. Payments are sent to your registered bKash/Nagad number or bank account.</p>
    </div>

    <div id="statusMsg" class="muted" style="text-align:center; margin-top:10px;"></div>
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

    function esc(s) {
      return String(s ?? "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
    }

    function fmtBDT(n) {
      const v = Number(n || 0);
      return "৳ " + v.toLocaleString("en-US", {
        maximumFractionDigits: 2
      });
    }

    function renderTable(rows) {
      const tbody = document.getElementById("earningsBody");
      if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="muted">No earnings yet.</td></tr>`;
        return;
      }
      tbody.innerHTML = rows.map(r => {
        const badgeClass = r.status === "Paid" ? "status-paid" : "status-pending";
        return `<tr>
          <td><span class="course-title">${esc(r.courseTitle)}</span></td>
          <td>${esc(r.students)}</td>
          <td>${esc(fmtBDT(r.totalEarned))}</td>
          <td><span class="status-badge ${badgeClass}">${esc(r.status)}</span></td>
        </tr>`;
      }).join("");
    }

    async function loadEarnings() {
      const data = await apiGet("/proj/api/teacher/earnings.php");
      document.getElementById("totalEarnings").textContent = fmtBDT(data.summary.totalEarnings);
      document.getElementById("monthEarnings").textContent = fmtBDT(data.summary.thisMonthEarnings);
      document.getElementById("monthPeriod").textContent = data.summary.thisMonthLabel || "This month";
      document.getElementById("pendingPayout").textContent = fmtBDT(data.summary.pendingPayout);
      document.getElementById("nextPayout").textContent = data.summary.nextPayoutLabel || "Next payout: 5th";
      document.getElementById("totalSales").textContent = String(data.summary.totalSales || 0);
      renderTable(data.byCourse || []);
      document.getElementById("statusMsg").textContent = "";
    }

    document.addEventListener("DOMContentLoaded", () => {
      loadEarnings().catch(err => {
        document.getElementById("statusMsg").textContent = "Failed to load earnings: " + err.message;
        document.getElementById("earningsBody").innerHTML = `<tr><td colspan="4" class="muted">Failed to load.</td></tr>`;
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