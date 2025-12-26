<?php
declare(strict_types=1);
session_start();

$postId = (int)($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Post</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .post-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .post-title {
            font-size: 32px;
            color: #2e6d3f;
            margin-bottom: 15px;
        }
        .post-meta {
            color: #777;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .post-content {
            line-height: 1.8;
            color: #333;
            font-size: 16px;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #2e6d3f;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="container">
        <div class="post-card" id="postCard">
            <p style="color: #666;">Loading post...</p>
        </div>
    </div>

    <script>
        const postId = <?= $postId ?>;
        
        async function loadPost() {
            try {
                const res = await fetch(`/proj/api/blog/public-get.php?id=${postId}`);
                const data = await res.json();
                
                if (!data.ok || !data.post) {
                    document.getElementById('postCard').innerHTML = '<p style="color:#a03030;">Post not found.</p>';
                    return;
                }
                
                const post = data.post;
                document.getElementById('postCard').innerHTML = `
                    <h1 class="post-title">${esc(post.title)}</h1>
                    <div class="post-meta">
                        By ${esc(post.authorName)} • ${esc(post.date)} • ${post.views} views
                    </div>
                    <div class="post-content">${post.content}</div>
                    <a href="/proj/blogs.php" class="back-link">← Back to Blogs</a>
                `;
            } catch (err) {
                document.getElementById('postCard').innerHTML = '<p style="color:#a03030;">Error loading post.</p>';
            }
        }
        
        function esc(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        
        loadPost();
    </script>
</body>
</html>

