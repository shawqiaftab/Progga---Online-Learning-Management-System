<?php
declare(strict_types=1);
session_start();

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /proj/login.html");
    exit;
}

require __DIR__ . '/config/db.php';

$userId = (int)$_SESSION['user_id'];
$userName = (string)($_SESSION['name'] ?? 'User');
$courseId = (int)($_GET['courseId'] ?? 0);

// Fetch course details
$course = null;
if ($courseId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? LIMIT 1");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Reviews - প্রজ্ঞা</title>
    <style>
        :root {
            --primary-color: #adca9d;
        }

        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff;
        }

        .page-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .course-header {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .course-header h1 {
            margin: 0 0 10px;
            color: #333;
            font-size: 28px;
        }

        .course-header p {
            margin: 0;
            color: #666;
            font-size: 16px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2e6d3f;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .review-section {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 40px;
        }

        .review-section h3 {
            margin: 0 0 20px;
            color: #333;
            font-size: 22px;
        }

        .rating-input {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }

        .star-rating {
            display: flex;
            gap: 5px;
        }

        .star {
            font-size: 32px;
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }

        .star.active,
        .star:hover {
            color: #FFD700;
        }

        .review-textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .review-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .submit-review-btn {
            padding: 12px 24px;
            background: #2e6d3f;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-review-btn:hover:not(:disabled) {
            background: #1e522a;
            transform: translateY(-2px);
        }

        .submit-review-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .review-message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            display: none;
        }

        .review-message.success {
            background: #d4e8d0;
            color: #2e6d3f;
            display: block;
        }

        .review-message.error {
            background: #f8d7da;
            color: #a03030;
            display: block;
        }

        .existing-reviews {
            margin-top: 40px;
        }

        .existing-reviews h3 {
            margin: 0 0 20px;
            color: #333;
            font-size: 22px;
        }

        .review-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s;
        }

        .review-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .reviewer-name {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .review-rating {
            color: #FFD700;
            font-size: 18px;
        }

        .review-comment {
            color: #555;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .review-date {
            font-size: 12px;
            color: #999;
        }

        .empty-reviews {
            text-align: center;
            padding: 40px;
            color: #777;
            border: 2px dashed #ddd;
            border-radius: 8px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .page-container {
                padding: 0 10px;
            }

            .course-header h1 {
                font-size: 22px;
            }

            .review-section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="page-container">
        <a href="/proj/mycourses.php" class="back-link">← Back to My Courses</a>

        <?php if ($course): ?>
            <div class="course-header">
                <h1><?= h($course['title']) ?></h1>
                <p><?= h($course['description'] ?? 'No description available') ?></p>
            </div>

            <!-- Write Review Section -->
            <div class="review-section">
                <h3>Write a Review</h3>

                <div class="rating-input">
                    <span>Your Rating:</span>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                    <span id="ratingValue">0</span>
                </div>

                <textarea 
                    id="reviewComment" 
                    class="review-textarea" 
                    placeholder="Share your experience with this course...&#10;&#10;What did you learn? Would you recommend it to others?"
                    maxlength="500"
                ></textarea>

                <button id="submitReviewBtn" class="submit-review-btn">Submit Review</button>

                <div id="reviewMessage" class="review-message"></div>
            </div>

            <!-- Existing Reviews -->
            <div class="existing-reviews">
                <h3>Student Reviews</h3>
                <div id="reviewsList" class="loading">Loading reviews...</div>
            </div>

        <?php else: ?>
            <div class="course-header">
                <h1>Course Not Found</h1>
                <p>The course you're looking for doesn't exist or has been removed.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        'use strict';

        const BASE = '/proj';
        let selectedRating = 0;
        const courseId = <?= $courseId ?>;

        // NULL CHECK: Verify all required elements exist
        const starRatingEl = document.getElementById('starRating');
        const ratingValueEl = document.getElementById('ratingValue');
        const reviewCommentEl = document.getElementById('reviewComment');
        const submitBtnEl = document.getElementById('submitReviewBtn');
        const reviewMessageEl = document.getElementById('reviewMessage');
        const reviewsListEl = document.getElementById('reviewsList');

        if (!starRatingEl || !ratingValueEl || !reviewCommentEl || !submitBtnEl || !reviewMessageEl || !reviewsListEl) {
            console.error('Review form elements not found');
            return;
        }

        // Star rating interaction
        const stars = starRatingEl.querySelectorAll('.star');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.getAttribute('data-rating'));
                ratingValueEl.textContent = selectedRating;
                updateStars(selectedRating);
            });

            star.addEventListener('mouseenter', function() {
                const hoverRating = parseInt(this.getAttribute('data-rating'));
                updateStars(hoverRating);
            });
        });

        starRatingEl.addEventListener('mouseleave', function() {
            updateStars(selectedRating);
        });

        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // Submit review
        if (submitBtnEl) {
            submitBtnEl.addEventListener('click', async function() {
                const comment = reviewCommentEl.value.trim();

                if (selectedRating === 0) {
                    showMessage('Please select a rating', 'error');
                    return;
                }

                if (!comment) {
                    showMessage('Please write a comment', 'error');
                    return;
                }

                try {
                    submitBtnEl.disabled = true;
                    showMessage('Submitting review...', 'success');

                    const response = await fetch(`${BASE}/api/reviews/submit.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({
                            courseId: courseId,
                            rating: selectedRating,
                            comment: comment
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        throw new Error(data.error || 'Failed to submit review');
                    }

                    showMessage(data.message || 'Review submitted successfully!', 'success');
                    reviewCommentEl.value = '';
                    selectedRating = 0;
                    updateStars(0);
                    ratingValueEl.textContent = '0';

                    // Reload reviews after 1 second
                    setTimeout(() => loadReviews(), 1000);

                } catch (error) {
                    showMessage(error.message, 'error');
                } finally {
                    submitBtnEl.disabled = false;
                }
            });
        }

        function showMessage(text, type) {
            if (!reviewMessageEl) return;
            reviewMessageEl.textContent = text;
            reviewMessageEl.className = `review-message ${type}`;

            if (type === 'success') {
                setTimeout(() => {
                    reviewMessageEl.style.display = 'none';
                }, 5000);
            }
        }

        // Load existing reviews
        async function loadReviews() {
            if (!reviewsListEl) return;

            try {
                const response = await fetch(`${BASE}/api/reviews/by-course.php?courseId=${courseId}`, {
                    credentials: 'include'
                });
                const data = await response.json();

                if (!data.ok || !data.reviews || data.reviews.length === 0) {
                    reviewsListEl.innerHTML = '<div class="empty-reviews"><p>No reviews yet. Be the first to review this course!</p></div>';
                    return;
                }

                reviewsListEl.innerHTML = data.reviews.map(review => `
                    <div class="review-card">
                        <div class="review-header">
                            <span class="reviewer-name">${escapeHtml(review.name || 'Anonymous')}</span>
                            <span class="review-rating">${'★'.repeat(Math.floor(review.rating))}${'☆'.repeat(5 - Math.floor(review.rating))}</span>
                        </div>
                        <p class="review-comment">${escapeHtml(review.comment)}</p>
                        ${review.createdat ? `<div class="review-date">${new Date(review.createdat).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>` : ''}
                    </div>
                `).join('');

            } catch (error) {
                console.error('Error loading reviews:', error);
                reviewsListEl.innerHTML = '<div class="empty-reviews"><p style="color: #a03030;">Failed to load reviews. Please refresh the page.</p></div>';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize
        if (courseId && courseId > 0) {
            loadReviews();
        } else {
            if (reviewsListEl) {
                reviewsListEl.innerHTML = '<div class="empty-reviews"><p style="color: #a03030;">Invalid course ID</p></div>';
            }
        }
    })();
    </script>
</body>
</html>
