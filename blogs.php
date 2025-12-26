<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Blogs</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h1 {
            font-size: 32px;
            color: #2e6d3f;
            margin-bottom: 10px;
        }
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        .blog-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .blog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .blog-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #2e6d3f;
        }
        .blog-excerpt {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .blog-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #777;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }
        .blog-category {
            display: inline-block;
            background: #eef5ed;
            color: #2e6d3f;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Community Blogs</h1>
            <p>Read insights and experiences from our community</p>
        </div>
        
        <div class="blog-grid" id="blogGrid">
            <p style="text-align: center;">Loading blogs...</p>
        </div>
    </div>

    <script>
        async function loadBlogs() {
            try {
                const res = await fetch('/proj/api/blog/public-list.php');
                const data = await res.json();
                
                if (!data.ok || !data.blogs || data.blogs.length === 0) {
                    document.getElementById('blogGrid').innerHTML = '<p style="text-align:center;color:#666;">No blog posts yet.</p>';
                    return;
                }
                
                document.getElementById('blogGrid').innerHTML = data.blogs.map(blog => `
                    <a href="/proj/blog-view.php?id=${blog.id}" class="blog-card">
                        <span class="blog-category">${esc(blog.category)}</span>
                        <h2 class="blog-title">${esc(blog.title)}</h2>
                        <p class="blog-excerpt">${esc(blog.excerpt)}</p>
                        <div class="blog-meta">
                            <span>By ${esc(blog.authorName)}</span>
                            <span>${esc(blog.date)}</span>
                        </div>
                    </a>
                `).join('');
            } catch (err) {
                document.getElementById('blogGrid').innerHTML = '<p style="text-align:center;color:#a03030;">Error loading blogs.</p>';
            }
        }
        
        function esc(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        
        loadBlogs();
    </script>
</body>
</html>

