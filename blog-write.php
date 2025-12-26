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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Write Blog Post - প্রজ্ঞা</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff;
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

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #adca9d;
        }

        .form-group textarea {
            min-height: 300px;
            line-height: 1.6;
            resize: vertical;
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

        .meta-group select {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
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
            .editor-container {
                width: 90%;
            }

            .meta-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="editor-container">
        <div class="page-header">
            <h1 id="pageTitle">Write a New Blog Post</h1>
            <a href="/proj/blog-dashboard.php" class="back-link">← Back to Blog</a>
        </div>

        <div class="form-group">
            <label for="postTitle">Post Title</label>
            <input type="text" id="postTitle" placeholder="e.g. How I Built My First Portfolio Website" />
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
            <label for="postContent">Content</label>
            <textarea id="postContent" placeholder="Start writing your blog post here..."></textarea>
            <div class="helper-bar">
                <span id="statusText">Not saved yet</span>
                <span id="wordCount">0 words</span>
            </div>
        </div>

        <div class="publish-info">
            💡 Posts are automatically saved as drafts. Click Publish when ready to share with the community!
        </div>

        <div class="action-buttons">
            <button class="btn-draft" id="btnSaveDraft">Save as Draft</button>
            <button class="btn-save" id="btnPublish">Publish Post</button>
        </div>
    </div>

    <script>
        const BASE = '/proj';

        async function apiGet(url) {
            const res = await fetch(url, { credentials: 'include' });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        async function apiPost(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        function getQueryParam(name) {
            const u = new URLSearchParams(window.location.search);
            return u.get(name);
        }

        function wordCount(text) {
            const t = String(text ?? '').trim();
            if (!t) return 0;
            return t.split(/\s+/).length;
        }

        function setStatus(msg) {
            document.getElementById('statusText').textContent = msg;
        }

        function updateWordCount() {
            document.getElementById('wordCount').textContent = 
                wordCount(document.getElementById('postContent').value) + ' words';
        }

        function getPayload(statusOverride) {
            return {
                id: getQueryParam('id'),
                title: document.getElementById('postTitle').value.trim(),
                category: document.getElementById('postCategory').value,
                status: statusOverride || 'draft',
                content: document.getElementById('postContent').value
            };
        }

        function validateMin() {
            const title = document.getElementById('postTitle').value.trim();
            const content = document.getElementById('postContent').value.trim();
            if (!title) throw new Error('Please enter a post title.');
            if (!content) throw new Error('Please write some content.');
        }

        async function loadForEditIfNeeded() {
            const id = getQueryParam('id');
            if (!id) return;

            document.getElementById('pageTitle').textContent = 'Edit Blog Post';
            setStatus('Loading...');

            const data = await apiGet(`${BASE}/api/blog/get.php?id=${encodeURIComponent(id)}`);
            document.getElementById('postTitle').value = data.post.title;
            document.getElementById('postCategory').value = data.post.category || 'Learning Journey';
            document.getElementById('postContent').value = data.post.content;
            updateWordCount();
            setStatus('Loaded');
        }

        async function save(statusOverride) {
            validateMin();
            setStatus('Saving...');

            const payload = getPayload(statusOverride);
            const out = await apiPost(`${BASE}/api/blog/save.php`, payload);

            if (!getQueryParam('id')) {
                const url = new URL(window.location.href);
                url.searchParams.set('id', out.id);
                history.replaceState(null, '', url.toString());
            }

            updateWordCount();
            setStatus(out.status === 'published' ? 'Published ✓' : 'Draft saved ✓');
            return out;
        }

        document.getElementById('postContent').addEventListener('input', updateWordCount);

        document.getElementById('btnSaveDraft').addEventListener('click', async () => {
            try {
                await save('draft');
            } catch (err) {
                alert(err.message);
                setStatus('Not saved');
            }
        });

        document.getElementById('btnPublish').addEventListener('click', async () => {
            try {
                await save('published');
                window.location.href = `${BASE}/blog-dashboard.php`;
            } catch (err) {
                alert(err.message);
                setStatus('Not saved');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            updateWordCount();
            loadForEditIfNeeded().catch(err => {
                alert(err.message);
                setStatus('Failed to load');
            });
        });
    </script>
</body>
</html>
