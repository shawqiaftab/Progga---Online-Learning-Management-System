<?php
declare(strict_types=1);
session_start();

$isLoggedIn = !empty($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্রজ্ঞা - Transform Your Learning Journey</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #adca9d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #2d3436;
            overflow-x: hidden;
            background: #f5f0e8;
        }

        /* Navigation */
        nav {
            background: rgba(245, 240, 232, 0.98);
            backdrop-filter: blur(10px);
            padding: 1.2rem 5%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(255, 107, 53, 0.1);
            transition: all 0.3s ease;
            border-bottom: 2px solid rgba(255, 107, 53, 0.1);
        }

        nav.scrolled {
            padding: 0.8rem 5%;
            box-shadow: 0 4px 30px rgba(255, 107, 53, 0.15);
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
            font-family: Georgia, serif;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            align-items: center;
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
            background: #adca9d;
            transition: width 0.3s;
            border-radius: 2px;
        }

        .nav-links a:hover {
            color: #adca9d;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-cta {
            display: flex;
            gap: 1rem;
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

        /* Hero Section */
        .hero {
            margin-top: 80px;
            min-height: 90vh;
            background: #f5f0e8;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 3rem 5%;
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            color: #2d3436;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeInUp 0.8s ease;
        }

        .hero-text h1 .highlight {
            color: #adca9d;
        }

        .hero-text p {
            font-size: 1.3rem;
            color: #636e72;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }

        .hero-buttons .btn {
            padding: 1.1rem 2.5rem;
            font-size: 1.1rem;
        }

        .btn-white {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-white:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(255, 107, 53, 0.3);
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
            animation: fadeInUp 0.8s ease 0.6s backwards;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
        }

        .stat-label {
            color: #636e72;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .hero-visual {
            position: relative;
            animation: fadeInRight 1s ease;
        }

        .hero-quote {
            text-align: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
            min-height: 120px;
        }

        #randomQuote {
            font-size: 1.1rem;
            color: #636e72;
            font-style: italic;
            margin: 0;
            line-height: 1.6;
        }

        #randomAuthor {
            font-size: 0.95rem;
            color: #999;
            margin-top: 0.5rem;
        }

        .illustration-container {
            position: relative;
            padding: 2rem;
        }

        .floating-card {
            display: block;
            text-decoration: none;
            color: inherit;
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .floating-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .card-icon {
            width: 55px;
            height: 55px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #9fc38c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-title {
            font-weight: 600;
            color: #2d3436;
            font-size: 1.1rem;
        }

        /* Features */
        .features {
            padding: 6rem 5%;
            background: white;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }

        .section-header h2 {
            font-size: 2.8rem;
            color: #2d3436;
            margin-bottom: 1rem;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #636e72;
            margin-top: 1.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .feature-card {
            background: #f5f0e8;
            padding: 2.5rem;
            border-radius: 25px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid rgba(255, 107, 53, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(255, 107, 53, 0.2);
            border-color: var(--primary-color);
        }

        .feature-icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #ff8c5a 100%);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2d3436;
        }

        .feature-card p {
            color: #636e72;
            line-height: 1.8;
        }

        /* Courses */
        .courses {
            padding: 6rem 5%;
            background: #f5f0e8;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .course-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(255, 107, 53, 0.15);
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(255, 107, 53, 0.25);
            border-color: var(--primary-color);
        }

        .course-image {
            height: 220px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #ff8c5a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3.5rem;
        }

        .course-content {
            padding: 2rem;
        }

        .course-category {
            display: inline-block;
            padding: 0.4rem 1.2rem;
            background: rgba(255, 107, 53, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }

        .course-card h3 {
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
            color: #2d3436;
        }

        .course-card p {
            color: #636e72;
            line-height: 1.7;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f5f0e8;
        }

        /* Testimonials */
        .testimonials {
            padding: 6rem 5%;
            background: linear-gradient(135deg, var(--primary-color) 0%, #ff8c5a 100%);
        }

        .testimonials .section-header h2 {
            color: white;
        }

        .testimonials .section-header p {
            color: rgba(255, 255, 255, 0.95);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 2.5rem;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .testimonial-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, #ff8c5a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .testimonial-info h4 {
            color: #2d3436;
            margin-bottom: 0.3rem;
        }

        .testimonial-info p {
            color: #636e72;
            font-size: 0.9rem;
        }

        .testimonial-text {
            color: #555;
            line-height: 1.8;
            font-style: italic;
        }

        .testimonial-rating {
            color: #adca9d;
            margin-top: 1.2rem;
        }

        /* CTA */
        .cta-section {
            padding: 6rem 5%;
            background: white;
            text-align: center;
        }

        .cta-content {
            max-width: 850px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
        }

        .cta-content h2 .highlight {
            color: #adca9d;
        }

        .cta-content p {
            font-size: 1.25rem;
            color: #636e72;
            margin-bottom: 2.5rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }

        /* Footer */
        footer {
            background: #2d3436;
            color: white;
            padding: 4rem 5% 2rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            color: #adca9d;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.8rem;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-section a:hover {
            color: #adca9d;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 968px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero-text h1 {
                font-size: 2.5rem;
            }
            .nav-links {
                display: none;
            }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-text">প্রজ্ঞা</span>
            </div>

            <?php if ($isLoggedIn): ?>
                <ul class="nav-links">
                    <li><a href="/proj/index.php">Home</a></li>
                    <li><a href="/proj/courses.php">Courses</a></li>
                    <li><a href="/proj/mycourses.php">My Courses</a></li>
                    <li><a href="/proj/<?= $_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin' ? 'teacher-dashboard.php' : 'student-dashboard.php' ?>">My Dashboard</a></li>
                    <li><a href="/proj/blog-dashboard.php">Blog</a></li>
                    <li><a href="/proj/discounts.php">Discount</a></li>
                </ul>

                <div class="nav-cta">
                    <a href="/proj/settings.php" class="btn btn-secondary">Profile</a>
                    <a href="/proj/api/auth/logout.php" class="btn btn-primary">Logout</a>
                </div>
            <?php else: ?>
                <ul class="nav-links">
                    <li><a href="/proj/courses.php">Courses</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Reviews</a></li>
                    <li><a href="/proj/blog-dashboard.php">Blogs</a></li>
                    <li><a href="#about">About</a></li>
                </ul>

                <div class="nav-cta">
                    <a href="/proj/login.html" class="btn btn-secondary">Login</a>
                    <a href="/proj/signup.html" class="btn btn-primary">Get Started</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Transform Your Future with <span class="highlight">প্রজ্ঞা</span></h1>
                <p>Join thousands of learners mastering new skills with expert-led courses. Learn at your own pace, anywhere, anytime.</p>
                <div class="hero-buttons">
                    <a href="/proj/signup.html" class="btn btn-primary">Start Learning Free</a>
                    <a href="/proj/courses.php" class="btn btn-white">Explore Courses</a>
                </div>

                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Active Students</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Expert Instructors</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Quality Courses</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-quote">
                    <p id="randomQuote"></p>
                    <p id="randomAuthor"></p>
                </div>

                <div class="illustration-container">
                    <a class="floating-card" href="/proj/course.php?id=1">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-code"></i></div>
                            <div class="card-title">Get Started with Web Development</div>
                        </div>
                    </a>

                    <a class="floating-card" href="/proj/course.php?id=2">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-brain"></i></div>
                            <div class="card-title">Machine Learning Fundamentals</div>
                        </div>
                    </a>

                    <a class="floating-card" href="/proj/course.php?id=3">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-palette"></i></div>
                            <div class="card-title">UI/UX Design Mastery</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="section-header">
            <h2>Why Choose প্রজ্ঞা</h2>
            <p>Experience a learning environment designed for your success with industry-leading features</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3>Expert Instructors</h3>
                <p>Learn from industry professionals with years of real-world experience and proven teaching methods.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-clock"></i></div>
                <h3>Learn Anytime</h3>
                <p>Access courses 24/7 from any device. Study at your own pace and fit learning into your schedule.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                <h3>Earn Certificates</h3>
                <p>Receive recognized certificates upon completion to showcase your achievements and boost your career.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <h3>Community Support</h3>
                <p>Join a vibrant community of learners. Collaborate, share ideas, and grow together.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-project-diagram"></i></div>
                <h3>Real Projects</h3>
                <p>Apply your knowledge with hands-on projects that prepare you for real-world challenges.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Support</h3>
                <p>Get help whenever you need it with our dedicated support team always ready to assist you.</p>
            </div>
        </div>
    </section>

    <!-- Courses -->
    <section class="courses" id="popular">
        <div class="section-header">
            <h2>Popular Courses</h2>
            <p>Explore our most sought-after courses designed to accelerate your career</p>
        </div>

        <div class="courses-grid" id="popularCoursesGrid">
            <div class="course-card">
                <div class="course-image"><i class="fas fa-spinner"></i></div>
                <div class="course-content">
                    <span class="course-category">Loading</span>
                    <h3>Loading courses...</h3>
                    <p>Please wait.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="section-header">
            <h2>What Our Students Say</h2>
            <p>Join thousands of satisfied learners who transformed their careers</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">SA</div>
                    <div class="testimonial-info">
                        <h4>Sarah Ahmed</h4>
                        <p>Software Developer</p>
                    </div>
                </div>
                <p class="testimonial-text">The courses are incredibly well-structured and the instructors are top-notch. I landed my dream job within 3 months of completing the bootcamp!</p>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">RK</div>
                    <div class="testimonial-info">
                        <h4>Rakib Khan</h4>
                        <p>Data Analyst</p>
                    </div>
                </div>
                <p class="testimonial-text">Best investment I've made in my career. The practical projects and community support made learning enjoyable and effective.</p>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">NI</div>
                    <div class="testimonial-info">
                        <h4>Nusrat Islam</h4>
                        <p>UX Designer</p>
                    </div>
                </div>
                <p class="testimonial-text">The flexibility to learn at my own pace while managing work was perfect. The certificate helped me get promoted!</p>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Start Your <span class="highlight">Learning Journey?</span></h2>
            <p>Join our community today and unlock access to world-class education. Start learning with a free trial and transform your future!</p>
            <div class="cta-buttons">
                <a href="/proj/signup.html" class="btn btn-primary" style="padding: 1.2rem 2.8rem; font-size: 1.1rem;">Start Free Trial</a>
                <a href="/proj/courses.php" class="btn btn-secondary" style="padding: 1.2rem 2.8rem; font-size: 1.1rem;">Browse All Courses</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content" id="about">
            <div class="footer-section">
                <h3>প্রজ্ঞা</h3>
                <p style="color: rgba(255,255,255,0.8);">Empowering learners worldwide with quality education and expert-led courses. Your journey to success starts here.</p>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="/proj/courses.php">All Courses</a></li>
                    <li><a href="/proj/teacher-dashboard.php">Become Instructor</a></li>
                    <li><a href="/proj/blog-dashboard.php">Blog</a></li>
                    <li><a href="#about">About Us</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 প্রজ্ঞা. All rights reserved. Made with <span style="color: #adca9d;">❤</span> in Bangladesh</p>
        </div>
    </footer>

    <script>
        async function apiGet(url) {
            const res = await fetch(url, { credentials: 'include' });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        function esc(s) {
            return (String(s) ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function pickIcon(category) {
            const c = (category || '').toLowerCase();
            if (c.includes('web')) return 'fa-laptop-code';
            if (c.includes('machine') || c.includes('data')) return 'fa-robot';
            if (c.includes('design') || c.includes('ui')) return 'fa-pen-fancy';
            if (c.includes('math')) return 'fa-square-root-variable';
            return 'fa-book-open';
        }

        function renderPopularCourses(courses) {
            const grid = document.getElementById('popularCoursesGrid');
            if (!grid) return;

            if (!courses.length) {
                grid.innerHTML = `
                    <div class="course-card">
                        <div class="course-image"><i class="fas fa-circle-info"></i></div>
                        <div class="course-content">
                            <span class="course-category">No courses</span>
                            <h3>No popular courses found</h3>
                            <p>Please add courses in the database.</p>
                        </div>
                    </div>
                `;
                return;
            }

            grid.innerHTML = courses.slice(0, 3).map(c => {
                const icon = pickIcon(c.category);
                const rating = c.ratingAvg ? Number(c.ratingAvg).toFixed(1) : '5.0';
                const reviews = c.ratingCount ? `${c.ratingCount} reviews` : 'No reviews';
                const students = c.enrollCount ? `${c.enrollCount} students` : 'New course';

                return `
                    <div class="course-card" onclick="window.location.href='/proj/course.php?id=${encodeURIComponent(c.id)}'">
                        <div class="course-image">
                            <i class="fas ${esc(icon)}"></i>
                        </div>
                        <div class="course-content">
                            <span class="course-category">${esc(c.category || 'Course')}</span>
                            <h3>${esc(c.title || 'Untitled course')}</h3>
                            <p>${esc((c.description || 'No description.').slice(0,110))}${c.description && c.description.length>110 ? '...' : ''}</p>
                            <div class="course-meta">
                                <span><i class="fas fa-star"></i> ${esc(rating)}</span>
                                <span><i class="fas fa-user-graduate"></i> ${esc(students)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function loadPopularCourses() {
            try {
                const data = await apiGet('/proj/api/courses/list.php');
                renderPopularCourses(data.courses);
            } catch (err) {
                console.error(err);
                const grid = document.getElementById('popularCoursesGrid');
                if (grid) {
                    grid.innerHTML = `
                        <div class="course-card">
                            <div class="course-image"><i class="fas fa-triangle-exclamation"></i></div>
                            <div class="course-content">
                                <span class="course-category">Error</span>
                                <h3>Failed to load courses</h3>
                                <p>${esc(err.message)}</p>
                            </div>
                        </div>
                    `;
                }
            }
        }

        // Bangla quotes that rotate every 15 seconds
        const banglaQuotes = [
            { text: "গ্রামের লোকের অনুমানশক্তি প্রখর। সকালে আকাশের দিকে চাহিয়া তাহারা বলিতে পারে বিকালে বৃষ্টি হইবে। বিকালে যদি নেহাৎ বৃষ্টি না-ই হয় সে অপরাধ অবশ্য আকাশের।", author: "— মানিক বন্দোপাধ্যায়" },
            { text: "শূন্যতাই জানো শুধু? শূন্যের ভিতরে এত ঢেউ আছে সেকথা জানো না?", author: "— শঙ্খ ঘোষ" },
            { text: "বাংলার বাতাসে বৈরাগ্য আছে, বুঝলে? কিছুক্ষণ চুপ করে খোলা হাওয়ায় বসে থাকলে মনে হতে শুরু করে-কে কার? কী হবে অনর্থক উন্নতির চেষ্টা করে?", author: "— তারাদাস বন্দ্যোপাধ্যায়" },
            { text: "কিছু নেই বিস্ময়ের, গ্রন্থ যদি কাটে শুধু কীটে।", author: "— সৈয়দ শামসুল হক" },
            { text: "ঝুঁকি নাও, পড়ে যাও, উঠে দাঁড়াও, তার পর আবার দে ছুট!", author: "— মৃণাল সেন" },
            { text: "সর্বদা তটস্থ হয়ে থাকাই কেরানি-জীবনে উন্নতির সোপান।", author: "— গৌরকিশোর ঘোষ" },
            { text: "কেও কারও মত হতে পারে না। সবাই হয় তার নিজের মত। তুমি হাজার চেষ্টা করেও তোমার চাচার বা বাবার মত হতে পারবে না। সব মানুষই আলাদা।", author: "— হুমায়ূন আহমেদ" },
            { text: "বৃক্ষের মতো হও, আর মরা পাতাগুলো ঝরে পড়তে দাও।", author: "— জালাল উদ্দিন রুমি" },
            { text: "প্রিয় নেতা আমার তারেক রহমান এই অসহ্য গরম আর সহ্য হয় না।", author: "— ঘষেটি" }
        ];

        function getRandomQuoteWithAuthor() {
            return banglaQuotes[Math.floor(Math.random() * banglaQuotes.length)];
        }

        function updateQuote() {
            const quoteEl = document.getElementById("randomQuote");
            const authorEl = document.getElementById("randomAuthor");
            if (quoteEl && authorEl) {
                const { text, author } = getRandomQuoteWithAuthor();
                quoteEl.textContent = text;
                authorEl.textContent = author;
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            // Show initial quote
            updateQuote();

            // Rotate quotes every 15 seconds
            setInterval(updateQuote, 15000);

            // Load courses
            loadPopularCourses();

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.getElementById('navbar');
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>
