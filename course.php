<?php

declare(strict_types=1);

session_start(); 
$cid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$loggedIn = !empty($_SESSION['user_id']);
$initial = strtoupper(substr((string)($_SESSION['name'] ?? 'U'), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Course</title>
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

    .course-container {
      width: 50%;
      margin: 40px auto;
    }

    .course-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .course-title {
      font-size: 26px;
      margin-bottom: 12px;
      color: #333;
    }

    .course-meta {
      color: #666;
      font-size: 14px;
      margin-bottom: 20px;
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
    }

    .course-description {
      font-size: 16px;
      line-height: 1.6;
      color: #444;
      margin-bottom: 25px;
    }

    .course-content h3 {
      font-size: 18px;
      margin: 20px 0 10px;
      color: #2e6d3f;
    }

    .course-content ul {
      padding-left: 20px;
      margin-bottom: 25px;
      color: #555;
    }

    .review-card {
      background: #fafafa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 12px;
      transition: all 0.3s;
    }

    .review-card:hover {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #eee;
      flex-wrap: wrap;
    }

    .action-btn {
      padding: 8px 16px;
      background: #f0f0f0;
      color: #333;
      text-decoration: none;
      font-size: 14px;
      border-radius: 4px;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
    }

    .action-btn:hover {
      background: #e0e0e0;
      transform: translateY(-2px);
    }

    .action-btn.buy {
      background: #2e6d3f;
      color: #fff;
    }

    .action-btn.buy:hover {
      background: #1e522a;
    }

    .report-link {
      color: #be723f;
      text-decoration: underline;
      font-size: 14px;
      margin-left: auto;
      align-self: center;
      cursor: pointer;
      transition: color 0.3s;
    }

    .report-link:hover {
      color: #a55a2a;
    }

    .muted {
      color: #777;
      font-size: 14px;
    }

    @media (max-width: 968px) {
      .nav-links {
        display: none;
      }

      .course-container {
        width: 90%;
      }
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="course-container">
    <img id="courseImage" src="https://th.bing.com/th/id/OIP.X86RhcqeMhneCXmGv_qKvAHaHa?w=200&h=200&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1" alt="Course" class="course-image">
    <h1 id="courseTitle" class="course-title">Loading...</h1>
    <div id="courseMeta" class="course-meta muted">Loading course info...</div>
    <div id="courseDescription" class="course-description muted">Please wait…</div>

    <div class="course-content">
      <h3>What You'll Learn</h3>
      <ul id="learnList">
        <li class="muted">Loading…</li>
      </ul>
    </div>

    <div style="margin: 30px 0; padding-top: 20px; border-top: 1px solid #eee;">
      <h3 style="color:#2e6d3f; margin-bottom: 15px;">Student Reviews</h3>

      <div style="display:flex; align-items:center; gap:15px; margin-bottom: 20px;">
        <div id="ratingAvg" style="font-size:28px; font-weight:bold; color:#2e6d3f;">–</div>
        <div>
          <div id="ratingMeta" style="color:#555; margin-bottom: 4px;">Loading ratings…</div>
          <div id="ratingStars" style="display:flex; gap:2px;"></div>
        </div>
      </div>

      <div id="sampleReviews" style="margin-top: 20px;">
        <div class="review-card">
          <div class="muted">Loading reviews…</div>
        </div>
      </div>

      <a id="allReviewsLink"
        href="/proj/reviews.php"
        style="display:inline-block; margin-top:15px; color:#2e6d3f; text-decoration:underline; font-weight:bold; font-size:14px;">
        View all reviews →
      </a>
    </div>

    <div class="action-buttons">
      <button id="buyBtn" class="action-btn buy" style="display:none;">Buy Course</button>
      <a id="takeTestsLink"
        class="action-btn"
        href="<?= $cid > 0 ? ('exam-portal.php?id=' . $cid) : '#' ?>">
        Take Tests
      </a>
      <a id="writeBlogLink" href="/proj/blog-write.php" class="action-btn">Write Blog</a>
      <a id="viewResultsLink" href="/proj/results.php" class="action-btn">View Results</a>
      <a id="leaveReviewLink" href="/proj/reviews.php" class="action-btn">Leave Review</a>

      <span class="report-link"
        onclick="window.location='mailto:support@mahorobakotoba.com?subject=Report%20Issue%20-%20Course'">
        Report
      </span>
    </div>

    <div id="statusMsg" class="muted" style="margin-top:10px;"></div>
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

    function getCourseId() {
      const sp = new URLSearchParams(window.location.search);
      const id = Number(sp.get("id"));


      if (!id || isNaN(id) || id <= 0) {
        alert("Invalid course ID");
        window.location.href = "exam-portal.php";
        return null;
      }

      return id;
    }


    function starsHtml(avg) {
      const full = Math.floor(avg);
      const hasHalf = (avg - full) >= 0.5;
      const empty = 5 - full - (hasHalf ? 1 : 0);

      const parts = [];
      for (let i = 0; i < full; i++) parts.push(`<span style="color:#2e6d3f;">★</span>`);
      if (hasHalf) parts.push(`<span style="color:#2e6d3f;">★</span>`);
      for (let i = 0; i < empty; i++) parts.push(`<span style="color:#ccc;">★</span>`);
      return parts.join("");
    }

    function esc(s) {
      return String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    const BASE = location.pathname.includes('/proj/') ? '/proj' : '';

    function setLinks(courseId) {
      document.getElementById('takeTestsLink').href =
        `${BASE}/exam-portal.php?id=${encodeURIComponent(courseId)}`;

      document.getElementById('writeBlogLink').href =
        `${BASE}/blog-write.php?courseId=${encodeURIComponent(courseId)}`;
      document.getElementById('viewResultsLink').href =
        `${BASE}/results.php?courseId=${encodeURIComponent(courseId)}`;
      document.getElementById('leaveReviewLink').href =
        `${BASE}/reviews.php?courseId=${encodeURIComponent(courseId)}`;
      document.getElementById('allReviewsLink').href =
        `${BASE}/reviews.php?courseId=${encodeURIComponent(courseId)}`;
    }



    async function loadCourse() {
      const courseId = getCourseId();
      if (!courseId) return;

      setLinks(courseId);


      const c = await apiGet(`/proj/api/courses/get.php?courseId=${encodeURIComponent(courseId)}`);

      document.title = c.course.title || "Course";
      document.getElementById("courseTitle").textContent = c.course.title || "Course";


      const imgUrl = c.course.thumbnailUrl || c.course.image;
      const fallbackImage = "https://th.bing.com/th/id/OIP.X86RhcqeMhneCXmGv_qKvAHaHa?w=200&h=200&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1";

      const imgElement = document.getElementById("courseImage");

      if (imgUrl) {
        imgElement.src = imgUrl;

        imgElement.onerror = function() {
          this.src = fallbackImage;
          this.onerror = null;
        };
      } else {
        imgElement.src = fallbackImage;
      }

      document.getElementById("courseDescription").textContent = c.course.description || "";

      const metaParts = [
        c.course.teacherName ? `By ${c.course.teacherName}` : null,
        c.course.durationWeeks ? `${c.course.durationWeeks} Weeks` : null,
        c.course.lessonsCount ? `${c.course.lessonsCount} Lessons` : null,
        c.course.difficulty ? `${c.course.difficulty}` : null
      ].filter(Boolean);

      document.getElementById("courseMeta").textContent = metaParts.join(" • ");

      const ul = document.getElementById("learnList");
      const pts = Array.isArray(c.course.learnPoints) ? c.course.learnPoints : [];
      ul.innerHTML = pts.length ?
        pts.map(x => `<li>${esc(x)}</li>`).join("") :
        `<li class="muted">No learning outcomes added yet.</li>`;


      const s = await apiGet(`/proj/api/reviews/summary.php?courseId=${encodeURIComponent(courseId)}`);
      const avg = Number(s.avg || 0);
      document.getElementById("ratingAvg").textContent = avg ? `${avg.toFixed(1)} ★` : "No ratings";
      document.getElementById("ratingMeta").textContent = `${s.ratings || 0} ratings • ${s.reviews || 0} reviews`;
      document.getElementById("ratingStars").innerHTML = starsHtml(avg);

      const r = await apiGet(`/proj/api/reviews/sample.php?courseId=${encodeURIComponent(courseId)}`);
      const box = document.getElementById("sampleReviews");
      const items = Array.isArray(r.reviews) ? r.reviews : [];
      box.innerHTML = items.length ? items.map(rv => `
        <div class="review-card">
          <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
            <strong>${esc(rv.name || "Student")}</strong>
            <span style="color:#2e6d3f;">${"★".repeat(Math.max(0, Math.min(5, Number(rv.rating || 0))))}</span>
          </div>
          <p style="margin:0; font-size:14px; color:#444;">${esc(rv.comment || "")}</p>
        </div>
      `).join("") : `
        <div class="review-card"><div class="muted">No reviews yet.</div></div>
      `;


      try {
        const e = await apiGet(`/proj/api/enrollments/status.php?courseId=${encodeURIComponent(courseId)}`);
        document.getElementById("buyBtn").style.display = e.enrolled ? "none" : "inline-block";
        document.getElementById("statusMsg").textContent = e.enrolled ? "You are enrolled." : "Not enrolled yet.";
      } catch (err) {
        document.getElementById("statusMsg").textContent = "Login to enroll and access course features.";
        document.getElementById("buyBtn").style.display = "inline-block";
      }

      document.getElementById("buyBtn").onclick = async () => {
        try {
          document.getElementById("statusMsg").textContent = "Processing purchase...";
          await apiPost("/proj/api/purchases/buy.php", {
            courseId
          });
          document.getElementById("statusMsg").textContent = "Purchase successful. You are enrolled now.";
          document.getElementById("buyBtn").style.display = "none";
        } catch (err) {
          alert(err.message);
          document.getElementById("statusMsg").textContent = "Purchase failed.";
        }
      };
    }
    document.addEventListener('DOMContentLoaded', () => {
      loadCourse().catch(err => console.error(err));
    });
    
    async function loadCourseReviews(courseId) {
    try {
        const res = await fetch(`/proj/api/reviews/sample.php?courseId=${courseId}`);
        const data = await res.json();
        
        if (data.ok && data.reviews && data.reviews.length > 0) {
            const reviewsHtml = data.reviews.map(r => `
                <div style="background: #fafafa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <strong>${esc(r.name)}</strong>
                        <span style="color: #FFD700;">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</span>
                    </div>
                    <p style="margin: 0; color: #555;">${esc(r.comment)}</p>
                </div>
            `).join('');
            
            // Find where to insert (create a reviews section if it doesn't exist)
            const courseDetail = document.querySelector('.course-detail-section'); // adjust selector
            if (courseDetail) {
                const reviewsSection = document.createElement('div');
                reviewsSection.innerHTML = `
                    <h2 style="margin-top: 40px; color: #2e6d3f;">Student Reviews</h2>
                    ${reviewsHtml}
                `;
                courseDetail.appendChild(reviewsSection);
            }
        }
    } catch (err) {
        console.error('Failed to load reviews:', err);
    }
}

// Call it when page loads (get courseId from your existing code)
const courseId = getCourseIdFromURL(); // your existing function
if (courseId) {
    loadCourseReviews(courseId);
}

function esc(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
</script>
  </script>
  <!-- Reviews Section -->
<div class="reviews-section" id="reviewsSection" style="max-width: 900px; margin: 40px auto; padding: 20px;">
    <h2 style="color: #2e6d3f; margin-bottom: 20px;">Student Reviews</h2>
    <div id="reviewsSummary" style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
        <div style="font-size: 14px; color: #666;">Loading reviews...</div>
    </div>
    <div id="reviewsList"></div>
</div>

<script>
// Make sure you have getCourseId function - adjust as needed
function getCourseId() {
    const params = new URLSearchParams(window.location.search);
    return parseInt(params.get('id') || params.get('courseId') || '0');
}

async function loadReviews() {
    const courseId = getCourseId();
    if (!courseId) return;

    try {
        // Load summary
        const summaryRes = await fetch(`/proj/api/reviews/summary.php?courseId=${courseId}`);
        const summaryData = await summaryRes.json();
        
        if (summaryData.ok) {
            const avg = parseFloat(summaryData.avg || 0).toFixed(1);
            const count = summaryData.ratings || 0;
            
            document.getElementById('reviewsSummary').innerHTML = `
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div>
                        <div style="font-size: 48px; font-weight: bold; color: #2e6d3f;">${avg}</div>
                        <div style="color: #FFD700; font-size: 24px;">${'★'.repeat(Math.round(avg))}${'☆'.repeat(5-Math.round(avg))}</div>
                    </div>
                    <div>
                        <div style="font-size: 18px; color: #333;">${count} Reviews</div>
                        <div style="color: #666; font-size: 14px;">Average rating</div>
                    </div>
                </div>
            `;
        }

        // Load sample reviews
        const reviewsRes = await fetch(`/proj/api/reviews/sample.php?courseId=${courseId}`);
        const reviewsData = await reviewsRes.json();
        
        if (reviewsData.ok && reviewsData.reviews && reviewsData.reviews.length > 0) {
            document.getElementById('reviewsList').innerHTML = reviewsData.reviews.map(r => `
                <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong style="color: #333;">${esc(r.name)}</strong>
                        <span style="color: #FFD700;">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</span>
                    </div>
                    <p style="margin: 0; color: #555; line-height: 1.6;">${esc(r.comment)}</p>
                </div>
            `).join('');
        } else {
            document.getElementById('reviewsList').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #666;">
                    No reviews yet. Be the first to review this course!
                </div>
            `;
        }
    } catch (err) {
        console.error('Failed to load reviews:', err);
        document.getElementById('reviewsSummary').innerHTML = `
            <div style="color: #a03030;">Failed to load reviews</div>
        `;
    }
}

function esc(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Load reviews when page loads
document.addEventListener('DOMContentLoaded', loadReviews);
</script>

</body>

</html>
