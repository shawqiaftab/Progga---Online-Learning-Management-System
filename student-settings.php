<?php
declare(strict_types=1);


session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'learner') {
  header('Location: /proj/login.html');
  exit;
}

require __DIR__ . '/config/db.php';

$userId = (int)$_SESSION['user_id'];
$message = '';


function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'profile') {
    $name  = trim((string)($_POST['fullName'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($name === '' || $email === '') {
      $message = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $message = 'Invalid email address.';
    } else {
      // duplicate email error
      try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $userId]);

        $_SESSION['name']  = $name;
        $_SESSION['email'] = $email;
        $message = 'Profile updated.';
      } catch (Throwable $e) {
        $message = 'Could not update profile (email may already be used).';
      }
    }

  } elseif ($action === 'prefs') {
    $difficulty = (string)($_POST['pref_difficulty'] ?? 'Beginner');
    $goal       = (string)($_POST['goal'] ?? 'Exam Preparation');
    $weekly     = (string)($_POST['weekly_time'] ?? 'Less than 5 hours');

    $allowedLevels = ['Beginner','Intermediate','Advanced'];
    if (!in_array($difficulty, $allowedLevels, true)) $difficulty = 'Beginner';

    $stmt = $pdo->prepare("
      UPDATE users
      SET pref_difficulty = ?, goal = ?, weekly_time = ?
      WHERE id = ?
    ");
    $stmt->execute([$difficulty, $goal, $weekly, $userId]);
    $message = 'Preferences updated.';

  } elseif ($action === 'notifications') {
    $notifyProgress = isset($_POST['notify_progress']) ? 1 : 0;
    $notifyReco     = isset($_POST['notify_reco']) ? 1 : 0;
    $notifyStreak   = isset($_POST['notify_streak']) ? 1 : 0;

    $stmt = $pdo->prepare("
      UPDATE users
      SET notify_progress = ?, notify_reco = ?, notify_streak = ?
      WHERE id = ?
    ");
    $stmt->execute([$notifyProgress, $notifyReco, $notifyStreak, $userId]);
    $message = 'Notification settings updated.';

  } elseif ($action === 'delete') {
    $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$userId]);

    session_destroy();
    header('Location: /proj/goodbye.html');
    exit;
  }
}

$stmt = $pdo->prepare("
  SELECT name, email, pref_difficulty, goal, weekly_time,
         notify_progress, notify_reco, notify_streak
  FROM users
  WHERE id = ?
  LIMIT 1
");
$stmt->execute([$userId]);
$u = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$name  = (string)($u['name'] ?? ($_SESSION['name'] ?? ''));
$email = (string)($u['email'] ?? ($_SESSION['email'] ?? ''));

$prefDifficulty = (string)($u['pref_difficulty'] ?? 'Beginner');
$goal           = (string)($u['goal'] ?? 'Exam Preparation');
$weeklyTime     = (string)($u['weekly_time'] ?? 'Less than 5 hours');

$notifyProgress = (int)($u['notify_progress'] ?? 1);
$notifyReco     = (int)($u['notify_reco'] ?? 1);
$notifyStreak   = (int)($u['notify_streak'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <title>Account Settings</title>

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #ffffff;
    }
    .navbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      padding: 0 30px;
      height: 60px;
      background: #adca9d;
      color: #fff;
      font-size: 18px;
    }
    .navbar a {
      color: white;
      margin-right: 20px;
      text-decoration: none;
      font-weight: bold;
    }
    .logo img {
      height: 100%;
      width: auto;
      object-fit: contain;
    }
    .logo {
      height: 100%;
      display: flex;
      align-items: center;
    }
    .auth-links {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .auth-links a {
      color: white;
      font-weight: bold;
      text-decoration: none;
    }
    .signup-btn {
      padding: 8px 15px;
      background: rgb(172, 82, 82);
      color: #2e6d3f;
      border-radius: 6px;
      font-weight: bold;
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
    }

    .btn-save:hover {
      background: #1e522a;
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
    }

    .btn-danger:hover {
      background: #8a2a2a;
    }

    .learning-preference {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .preference-btn {
      padding: 8px 16px;
      background: #e0e0e0;
      border: 1px solid #ccc;
      border-radius: 20px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s;
    }

    .preference-btn.active {
      background: #eef5ed;
      border-color: #2e6d3f;
      color: #2e6d3f;
      font-weight: bold;
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

    input:checked + .slider {
      background-color: #2e6d3f;
    }

    input:checked + .slider:before {
      transform: translateX(26px);
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
        <li><a href="/proj/student-dashboard.php">My Dashboard</a></li>
        <li><a href="/proj/discounts.php">Discounts</a></li>
      </ul>

      <div class="nav-cta">
        <form method="post" action="/proj/api/auth/logout.php" style="margin:0;">
          <button class="btn btn-secondary" type="submit">Logout</button>
        </form>
      </div>
    </div>
  </nav>

  <div class="settings-container">
    <div class="page-header">
      <h1>Account Settings</h1>
      <a href="/proj/student-dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
      <p style="color:#2e6d3f; font-weight:bold;"><?= h($message) ?></p>
    <?php endif; ?>

    <!-- Profile Settings -->
    <div class="settings-section">
      <h2 class="section-title">Profile Information</h2>
      <form method="post">
        <input type="hidden" name="action" value="profile">
        <div class="form-row">
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" name="fullName" value="<?= h($name) ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= h($email) ?>" required>
          </div>
        </div>
        <button class="btn-save" type="submit">Save Changes</button>
      </form>
    </div>

    <!-- Learning Preferences -->
    <div class="settings-section">
      <h2 class="section-title">Learning Preferences</h2>
      <form method="post">
        <input type="hidden" name="action" value="prefs">

        <div class="form-group">
          <label>Preferred Difficulty Level</label>
          <input type="hidden" id="pref_difficulty" name="pref_difficulty" value="<?= h($prefDifficulty) ?>">
          <div class="learning-preference">
            <?php
              $levels = ['Beginner','Intermediate','Advanced'];
              foreach ($levels as $lvl):
                $active = ($prefDifficulty === $lvl) ? 'active' : '';
            ?>
              <div class="preference-btn <?= $active ?>" data-level="<?= h($lvl) ?>"><?= h($lvl) ?></div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label>Learning Goals</label>
          <select name="goal">
            <?php
              $goals = ['Exam Preparation','Job Readiness','Personal Interest','Skill Upgrade'];
              foreach ($goals as $g):
            ?>
              <option value="<?= h($g) ?>" <?= $goal === $g ? 'selected' : '' ?>><?= h($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Weekly Study Time</label>
          <select name="weekly_time">
            <?php
              $times = ['Less than 5 hours','5–10 hours','10–20 hours','More than 20 hours'];
              foreach ($times as $t):
            ?>
              <option value="<?= h($t) ?>" <?= $weeklyTime === $t ? 'selected' : '' ?>><?= h($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button class="btn-save" type="submit">Save Preferences</button>
      </form>
    </div>

    <!-- Notification -->
    <div class="settings-section">
      <h2 class="section-title">Notification Settings</h2>
      <form method="post">
        <input type="hidden" name="action" value="notifications">

        <div class="notification-item">
          <span>Course progress reminders</span>
          <label class="switch">
            <input type="checkbox" name="notify_progress" <?= $notifyProgress ? 'checked' : '' ?>>
            <span class="slider"></span>
          </label>
        </div>

        <div class="notification-item">
          <span>New course recommendations</span>
          <label class="switch">
            <input type="checkbox" name="notify_reco" <?= $notifyReco ? 'checked' : '' ?>>
            <span class="slider"></span>
          </label>
        </div>

        <div class="notification-item">
          <span>Weekly learning streak alerts</span>
          <label class="switch">
            <input type="checkbox" name="notify_streak" <?= $notifyStreak ? 'checked' : '' ?>>
            <span class="slider"></span>
          </label>
        </div>

        <button class="btn-save" type="submit">Save Settings</button>
      </form>
    </div>

    <!-- Privacy -->
    <div class="settings-section">
      <h2 class="section-title">Privacy & Data</h2>
      <p style="color: #555; margin-bottom: 15px;">
        Your learning activity is used only to improve course recommendations and platform experience.
      </p>
      <form method="post" action="/proj/export-data.php">
        <button class="btn-save" type="submit">Download My Data</button>
      </form>
    </div>

    <!-- Danger Zone -->
    <div class="settings-section">
      <h2 class="section-title">Danger Zone</h2>
      <p style="color: #666; margin-bottom: 15px;">
        Permanently delete your account and all learning progress.
      </p>
      <form method="post" onsubmit="return confirm('Are you sure? This cannot be undone.');">
        <input type="hidden" name="action" value="delete">
        <button class="btn-danger" type="submit">Delete My Account</button>
      </form>
    </div>
  </div>

  <script>
    document.querySelectorAll('.preference-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        this.parentElement.querySelectorAll('.preference-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('pref_difficulty').value = this.getAttribute('data-level');
      });
    });
  </script>
</body>
</html>
