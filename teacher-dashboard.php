<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /proj/login.html");
    exit;
}

if (($_SESSION['role'] ?? 'learner') !== 'teacher' && ($_SESSION['role'] ?? 'learner') !== 'admin') {
    header("Location: /proj/student-dashboard.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$userName = (string)($_SESSION['name'] ?? 'Teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #f5f5f5;
        }
        .dashboard-container {
            max-width: 1200px;
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
            font-size: 28px;
            margin: 0;
        }
        .header-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-primary {
            background: #2e6d3f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            display: inline-block;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            background: #1e522a;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            display: inline-block;
            transition: all 0.3s;
            border: 1px solid #ccc;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .section-title {
            font-size: 22px;
            margin: 30px 0 20px;
            color: #333;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .course-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
            background: white;
        }
        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .course-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            background: #e0e0e0;
        }
        .course-content {
            padding: 15px;
        }
        .course-content h3 {
            margin: 0 0 8px;
            font-size: 18px;
            color: #333;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .course-actions {
            display: flex;
            gap: 8px;
        }
        .action-btn {
            flex: 1;
            padding: 8px 10px;
            font-size: 13px;
            text-align: center;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        .action-btn.edit {
            background: #d4e8d0;
            color: #2e6d3f;
        }
        .action-btn.edit:hover {
            background: #c0dcb8;
        }
        .action-btn.delete {
            background: #f8d7da;
            color: #a03030;
        }
        .action-btn.delete:hover {
            background: #f0bfbf;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            border: 1px dashed #ddd;
            border-radius: 8px;
        }
        .empty-state h3 {
            margin: 0 0 15px;
            color: #333;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .header-buttons {
                flex-direction: column;
                width: 100%;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Teacher Dashboard</h1>
            <div class="header-buttons">
                <a href="/proj/create-course.php" class="btn-primary">+ Create New Course</a>
                <a href="/proj/teacher-course-mcq-video.php" class="btn-secondary">Manage Content</a>
            </div>
        </div>
        
        <h2 class="section-title">My Courses</h2>
        <div class="course-grid" id="courseGrid">
            <div class="loading">Loading your courses...</div>
        </div>
    </div>

    <script>
        async function loadCourses() {
            try {
                const res = await fetch('/proj/api/teacher/courses.php', {
                    credentials: 'include'
                });
                const data = await res.json();
                
                if (!data.ok || !data.courses) {
                    document.getElementById('courseGrid').innerHTML = '<div class="empty-state"><h3>Error loading courses</h3><p>' + (data.error || 'Unknown error') + '</p></div>';
                    return;
                }
                
                if (data.courses.length === 0) {
                    document.getElementById('courseGrid').innerHTML = `
                        <div class="empty-state">
                            <h3>No courses yet</h3>
                            <p>Create your first course to get started!</p>
                            <a href="/proj/create-course.php" class="btn-primary" style="margin-top: 15px; display: inline-block;">+ Create Your First Course</a>
                        </div>
                    `;
                    return;
                }
                
                document.getElementById('courseGrid').innerHTML = data.courses.map(course => `
                    <div class="course-card" data-course-id="${course.id}">
                        ${course.thumbnailUrl ? 
                            `<img src="${esc(course.thumbnailUrl)}" alt="${esc(course.title)}">` : 
                            '<img src="https://via.placeholder.com/300x140/e0e0e0/999999?text=No+Image" alt="Course">'
                        }
                        <div class="course-content">
                            <h3>${esc(course.title)}</h3>
                            <div class="course-meta">
                                <span>${esc(course.category || 'General')}</span>
                            </div>
                            <div class="course-actions">
                                <a href="/proj/teacher-course-mcq-video.php?courseId=${course.id}" class="action-btn edit">Edit</a>
                                <button class="action-btn delete" onclick="deleteCourse(${course.id}, '${esc(course.title)}')">Delete</button>
                            </div>
                        </div>
                    </div>
                `).join('');
                
            } catch (err) {
                console.error('Load error:', err);
                document.getElementById('courseGrid').innerHTML = '<div class="empty-state"><h3>Error</h3><p>Failed to load courses.</p></div>';
            }
        }
        
        async function deleteCourse(courseId, courseTitle) {
            if (!confirm(`Are you sure you want to delete "${courseTitle}"? This cannot be undone.`)) {
                return;
            }
            
            try {
                console.log('Deleting course:', courseId);
                
                const res = await fetch('/proj/api/courses/delete.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ courseId: courseId })
                });
                
                const data = await res.json();
                console.log('Delete response:', data);
                
                if (data.ok) {
                    alert('Course deleted successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete course'));
                }
            } catch (err) {
                console.error('Delete error:', err);
                alert('Error: Failed to delete course');
            }
        }
        
        function esc(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        
        loadCourses();
    </script>
</body>
</html>

