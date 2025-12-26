<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /proj/login.html");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$userName = (string)($_SESSION['name'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Blog - প্রজ্ঞা</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff;
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
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
        }

        .action-btn {
            padding: 6px 12px;
            font-size: 13px;
            background: #e0e0e0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            flex: 1;
            transition: all 0.3s;
        }

        .action-btn.edit {
            background: #d4e8d0;
            color: #2e6d3f;
        }

        .action-btn.delete {
            background: #f8d7da;
            color: #a03030;
        }

        .action-btn:hover {
            transform: translateY(-2px);
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

        #loadingMsg {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="blog-container">
        <div class="dashboard-header">
            <h1>My Blog Posts</h1>
            <a href="/proj/blog-write.php" class="new-post-btn">✍️ New Post</a>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <h3>TOTAL POSTS</h3>
                <p id="totalPosts">-</p>
            </div>
            <div class="stat-card">
                <h3>PUBLISHED</h3>
                <p id="publishedCount">-</p>
            </div>
            <div class="stat-card">
                <h3>DRAFTS</h3>
                <p id="draftsCount">-</p>
            </div>
            <div class="stat-card">
                <h3>TOTAL VIEWS</h3>
                <p id="totalViews">-</p>
            </div>
        </div>

        <div id="loadingMsg">Loading your posts...</div>

        <div id="publishedSection" style="display:none;">
            <h2 class="section-title">Published Posts</h2>
            <div class="post-grid" id="publishedGrid"></div>
        </div>

        <div id="draftsSection" style="display:none;">
            <h2 class="section-title">Drafts</h2>
            <div class="post-grid" id="draftsGrid"></div>
        </div>
    </div>

    <script>
        const BASE = '/proj';

        async function apiGet(url) {
            const res = await fetch(url, { credentials: 'include' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        async function apiPost(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        function esc(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function formatDate(dateStr) {
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function renderPost(post) {
            const statusClass = post.status === 'published' ? 'status-published' : 'status-draft';
            const statusText = post.status === 'published' ? 'Published' : 'Draft';

            return `
                <div class="post-card">
                    <div class="post-content">
                        <span class="post-status ${statusClass}">${statusText}</span>
                        <h3>${esc(post.title)}</h3>
                        <p class="post-excerpt">${esc(post.excerpt)}</p>
                        <div class="post-meta">
                            <span>${formatDate(post.createdAt)}</span>
                            <span>${post.views || 0} views</span>
                        </div>
                        <div class="post-actions">
                            <button class="action-btn edit" onclick="editPost(${post.id})">Edit</button>
                            ${post.status === 'draft' ? 
                                `<button class="action-btn delete" onclick="deletePost(${post.id})">Delete</button>` : 
                                `<button class="action-btn" onclick="viewPost(${post.id})">View</button>`}
                        </div>
                    </div>
                </div>
            `;
        }

        async function loadPosts() {
            try {
                const data = await apiGet(`${BASE}/api/blog/list.php`);

                document.getElementById('totalPosts').textContent = data.stats.totalPosts;
                document.getElementById('publishedCount').textContent = data.stats.published;
                document.getElementById('draftsCount').textContent = data.stats.drafts;
                document.getElementById('totalViews').textContent = data.stats.totalViews;

                document.getElementById('loadingMsg').style.display = 'none';

                if (data.published.length > 0) {
                    document.getElementById('publishedSection').style.display = 'block';
                    document.getElementById('publishedGrid').innerHTML = data.published.map(renderPost).join('');
                }

                if (data.drafts.length > 0) {
                    document.getElementById('draftsSection').style.display = 'block';
                    document.getElementById('draftsGrid').innerHTML = data.drafts.map(renderPost).join('');
                }

                if (data.published.length === 0 && data.drafts.length === 0) {
                    document.getElementById('loadingMsg').innerHTML = `
                        <div class="empty-state">
                            <h3>No blog posts yet</h3>
                            <p>Start writing your first post by clicking "New Post" above.</p>
                        </div>
                    `;
                    document.getElementById('loadingMsg').style.display = 'block';
                }
            } catch (err) {
                document.getElementById('loadingMsg').textContent = 'Error loading posts: ' + err.message;
            }
        }

        function editPost(id) {
            window.location.href = `${BASE}/blog-write.php?id=${id}`;
        }

        function viewPost(id) {
            window.location.href = `${BASE}/blog-view.php?id=${id}`;
        }

        async function deletePost(id) {
            if (!confirm('Are you sure you want to delete this draft?')) return;

            try {
                await apiPost(`${BASE}/api/blog/delete.php`, { id });
                alert('Post deleted successfully');
                loadPosts();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        document.addEventListener('DOMContentLoaded', loadPosts);
    </script>
</body>
</html>
