<?php

declare(strict_types=1);

require __DIR__ . "/api/auth/guard.php";

$name = (string)($_SESSION["name"] ?? "User");
$email = (string)($_SESSION["email"] ?? "");
$role = (string)($_SESSION["role"] ?? "learner");

$initial = strtoupper(substr($name !== "" ? $name : "U", 0, 1));


$dashboard = ($role === "teacher" || $role === "admin") ? "/proj/teacher-dashboard.php" : "/proj/student-dashboard.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account Settings</title>
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
      padding: 8px 14px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 700;
      border: none;
      cursor: pointer;
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

    .settings-container {
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

    .settings-section {
      background: #fafafa;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 25px;
      margin-bottom: 30px;
    }

    .section-title {
      font-size: 18px;
      margin: 0 0 20px;
      color: #2e6d3f;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
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
      transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-color);
    }

    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
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

    .btn-danger {
      background: #a03030;
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.3s;
    }

    .btn-danger:hover {
      background: #8a2a2a;
      transform: translateY(-2px);
    }

    .payment-info {
      background: #f0f7ef;
      padding: 15px;
      border-radius: 6px;
      margin-top: 10px;
      font-size: 14px;
      color: #555;
    }

    .notification-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #eee;
    }

    .notification-item:last-child {
      border-bottom: none;
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }

    .switch input {
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

    .status {
      text-align: center;
      margin-top: 10px;
      font-size: 14px;
      color: #777;
      min-height: 18px;
    }

    .status.ok {
      color: #2e6d3f;
    }

    .status.err {
      color: #a03030;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .settings-container {
        width: 95%;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
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
        <a href="/proj/index.php" style="text-decoration:none;">
          <span class="logo-text">প্রজ্ঞা</span>
        </a>
      </div>

      <ul class="nav-links">
        <li><a href="/proj/index.php#home">Home</a></li>
        <li><a href="/proj/courses.php">Courses</a></li>
        <li><a href="<?= htmlspecialchars($dashboard) ?>">My Dashboard</a></li>
        <li><a href="/proj/discounts.php">Discounts</a></li>
      </ul>

      <div class="nav-cta">
        <div class="user-avatar"><?= htmlspecialchars($initial) ?></div>
        <button class="logout-btn" id="logoutBtn">Logout</button>
      </div>
    </div>
  </nav>

  <div class="settings-container">
    <div class="page-header">
      <h1>Account Settings</h1>
      <a href="<?= htmlspecialchars($dashboard) ?>" class="back-link">← Back to Dashboard</a>
    </div>

    <!-- Profile Settings -->
    <div class="settings-section">
      <h2 class="section-title">Profile Information</h2>
      <div class="form-row">
        <div class="form-group">
          <label for="fullName">Full Name</label>
          <input type="text" id="fullName" value="<?= htmlspecialchars($name) ?>">
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" value="<?= htmlspecialchars($email) ?>" disabled>
        </div>
      </div>
      <div class="form-group">
        <label for="bio">Bio (Public)</label>
        <textarea id="bio" rows="3" placeholder="Write a short bio..."></textarea>
      </div>
      <button class="btn-save" id="saveProfileBtn">Save Changes</button>
      <div id="profileStatus" class="status"></div>
    </div>

 
    <div class="settings-section">
      <h2 class="section-title">Export Your Data</h2>
      <p style="color:#666; margin:0 0 12px;">Download your account data as JSON or CSV.</p>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a class="btn btn-primary" href="/proj/export-data.php?format=json&download=1">Download JSON</a>
        <a class="btn btn-secondary" href="/proj/export-data.php?format=csv&download=1">Download CSV</a>
      </div>
    </div>


    <?php if ($role === "teacher" || $role === "admin"): ?>
      <div class="settings-section">
        <h2 class="section-title">Payout Settings</h2>
        <div class="form-group">
          <label>Payout Method</label>
          <select id="payoutMethod">
            <option>bKash</option>
            <option>Nagad</option>
            <option>Bank Transfer</option>
          </select>
        </div>
        <div class="form-group">
          <label>Account / Mobile Number</label>
          <input id="payoutAccount" type="text" placeholder="Enter your bKash/Nagad number or bank account">
        </div>
        <div class="payment-info">💡 Payouts are processed on the 5th of each month for earnings above ৳ 500.</div>
        <button class="btn-save" id="savePayoutBtn">Update Payout Info</button>
        <div id="payoutStatus" class="status"></div>
      </div>
    <?php endif; ?>

    <div class="settings-section">
      <h2 class="section-title">Notification Preferences</h2>

      <div class="notification-item">
        <span>New student enrollment</span>
        <label class="switch">
          <input id="notifEnrollment" type="checkbox" checked>
          <span class="slider"></span>
        </label>
      </div>

      <div class="notification-item">
        <span>New course review</span>
        <label class="switch">
          <input id="notifReview" type="checkbox" checked>
          <span class="slider"></span>
        </label>
      </div>

      <div class="notification-item">
        <span>Weekly summary</span>
        <label class="switch">
          <input id="notifWeekly" type="checkbox">
          <span class="slider"></span>
        </label>
      </div>

      <button class="btn-save" id="saveNotifBtn">Save Preferences</button>
      <div id="notifStatus" class="status"></div>
    </div>

    <!-- Danger Zone -->
    <div class="settings-section">
      <h2 class="section-title">Danger Zone</h2>
      <p style="color:#666; margin-bottom: 15px;">Permanently delete your account and all associated data.</p>
      <button class="btn-danger" id="deleteAccountBtn">Delete Account</button>
      <div id="dangerStatus" class="status"></div>
    </div>
  </div>

  <script>
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


    document.getElementById("logoutBtn").addEventListener("click", async () => {
      try {
        const res = await fetch("/proj/logout.php", {
          method: "POST",
          credentials: "include"
        });
        const out = await res.json().catch(() => ({}));
        window.location.href = out.redirect || "/proj/login.html";
      } catch (e) {
        window.location.href = "/proj/login.html";
      }
    });


    function setStatus(id, msg, ok) {
      const el = document.getElementById(id);
      el.className = "status " + (ok ? "ok" : "err");
      el.textContent = msg;
    }

    document.getElementById("saveProfileBtn").addEventListener("click", () => {
      setStatus("profileStatus", "Saved (demo). Add an API endpoint to persist changes.", true);
    });

    const payoutBtn = document.getElementById("savePayoutBtn");
    if (payoutBtn) payoutBtn.addEventListener("click", () => {
      setStatus("payoutStatus", "Saved (demo). Add an API endpoint to persist changes.", true);
    });

    document.getElementById("saveNotifBtn").addEventListener("click", () => {
      setStatus("notifStatus", "Saved (demo). Add an API endpoint to persist changes.", true);
    });

    document.getElementById("deleteAccountBtn").addEventListener("click", () => {
      if (!confirm("Are you sure you want to delete your account? This cannot be undone.")) return;
      setStatus("dangerStatus", "Delete not implemented yet. Add a delete endpoint.", false);
    });
  </script>
</body>

</html>