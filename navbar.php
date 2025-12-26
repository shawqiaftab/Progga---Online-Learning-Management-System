<?php
// navbar.php - Fixed with consistent navigation
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$userName = $_SESSION['name'] ?? 'User';
$userInitial = strtoupper(mb_substr($userName, 0, 1));
?>

<style>
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

.logo-text {
    font-size: 2rem;
    font-weight: 700;
    color: #adca9d;
    font-family: Georgia, serif;
    text-decoration: none;
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
    content: "";
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
    transition: all 0.3s;
}

.logout-btn:hover {
    background: #f1b0b7;
}

.btn {
    padding: 0.8rem 1.8rem;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 0.95rem;
}

.btn-primary {
    background: #adca9d;
    color: white;
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
}

.btn-secondary {
    background: transparent;
    color: #adca9d;
    border: 2px solid #adca9d;
}

.btn-secondary:hover {
    background: #adca9d;
    color: white;
}

@media (max-width: 968px) {
    .nav-links {
        display: none;
    }
}
</style>

<nav id="navbar">
    <div class="nav-container">
        <div class="logo">
            <a href="/proj/index.php" class="logo-text">প্রজ্ঞা</a>
        </div>

        <?php if ($isLoggedIn): ?>
            <!-- Logged In Navigation - CONSISTENT FOR ALL -->
            <ul class="nav-links">
                <li><a href="/proj/index.php">Home</a></li>
                <li><a href="/proj/courses.php">Courses</a></li>
                <li><a href="/proj/mycourses.php">My Courses</a></li>
                <li><a href="/proj/<?= $userRole === 'teacher' || $userRole === 'admin' ? 'teacher-dashboard.php' : 'student-dashboard.php' ?>">My Dashboard</a></li>
                <li><a href="/proj/blog-dashboard.php">My Blogs</a></li>
                <li><a href="/proj/blogs.php">Blogs</a></li>
                <li><a href="/proj/discounts.php">Discount</a></li>
            </ul>

            <div class="nav-cta">
                <div class="user-avatar"><?= htmlspecialchars($userInitial) ?></div>
                <a href="/proj/api/auth/logout.php" class="logout-btn">Logout</a>
            </div>
        <?php else: ?>
            <!-- Logged Out Navigation -->
            <ul class="nav-links">
                <li><a href="/proj/index.php">Home</a></li>
                <li><a href="/proj/courses.php">Courses</a></li>
                <li><a href="/proj/blogs.php">Blogs</a></li>
                <li><a href="/proj/discounts.php">Discount</a></li>
            </ul>

            <div class="nav-cta">
                <a href="/proj/login.html" class="btn btn-secondary">Login</a>
                <a href="/proj/signup.html" class="btn btn-primary">Sign Up</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
