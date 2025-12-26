<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /proj/login.html");
    exit;
}

$name = (string)($_SESSION['name'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: #f5f5f5;
        }
        .results-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .results-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .results-header h1 {
            font-size: 28px;
            color: #333;
            margin: 0 0 10px;
        }
        .results-header p {
            color: #666;
            margin: 0;
        }
        .score-card {
            background: #f0f7ef;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            border: 2px solid #d4e8d0;
        }
        .score-value {
            font-size: 72px;
            font-weight: bold;
            color: #2e6d3f;
            margin: 10px 0;
            line-height: 1;
        }
        .score-text {
            font-size: 18px;
            color: #555;
            margin: 15px 0;
        }
        .score-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
            flex-wrap: wrap;
        }
        .stat-item strong {
            display: block;
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }
        .section-title {
            font-size: 22px;
            margin: 30px 0 20px;
            color: #333;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .question-list {
            margin-bottom: 30px;
        }
        .question-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .question-number {
            font-weight: bold;
            color: #2e6d3f;
        }
        .question-status {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
            white-space: nowrap;
        }
        .status-correct {
            background: #d4e8d0;
            color: #2e6d3f;
        }
        .status-incorrect {
            background: #f8d7da;
            color: #a03030;
        }
        .question-text {
            margin: 0 0 15px;
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }
        .answer-choice {
            padding: 10px 12px;
            margin: 6px 0;
            border-radius: 4px;
            font-size: 15px;
        }
        .answer-correct {
            background: #d4e8d0;
            color: #2e6d3f;
            font-weight: bold;
        }
        .answer-incorrect {
            background: #f8d7da;
            color: #a03030;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: #2e6d3f;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            font-size: 16px;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #1e522a;
            transform: translateY(-2px);
        }
        .error {
            color: #a03030;
            text-align: center;
            padding: 40px;
            background: #f8d7da;
            border-radius: 8px;
            margin: 20px 0;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        @media (max-width: 768px) {
            .results-container {
                width: 95%;
            }
            .score-value {
                font-size: 48px;
            }
            .score-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="results-container">
        <div class="results-header">
            <h1 id="examTitle">Loading Results...</h1>
            <p id="courseTitle"></p>
        </div>
        
        <div class="score-card" id="scoreCard">
            <div class="loading">Loading your score...</div>
        </div>
        
        <h2 class="section-title" id="questionsTitle" style="display: none;">Question Review</h2>
        <div class="question-list" id="questionList"></div>
        
        <div class="action-buttons">
            <a href="/proj/student-dashboard.php" class="btn-primary">Back to Dashboard</a>
            <a href="/proj/courses.php" class="btn-primary" style="background: #f0f0f0; color: #333; border: 1px solid #ccc;">Browse More Courses</a>
        </div>
    </div>

    <script>
        const API_RESULT = '/proj/api/exams/result.php';
        
        function esc(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        
        async function loadResults() {
            const urlParams = new URLSearchParams(window.location.search);
            const attemptId = urlParams.get('attemptId');
            
            console.log('Loading results for attemptId:', attemptId);
            
            if (!attemptId) {
                document.getElementById('scoreCard').innerHTML = '<div class="error">❌ Missing attemptId in URL. Please start from the exam portal.</div>';
                return;
            }
            
            try {
                const res = await fetch(`${API_RESULT}?attemptId=${encodeURIComponent(attemptId)}`, {
                    credentials: 'include'
                });
                
                const data = await res.json();
                console.log('API Response:', data);
                
                if (!data.ok || !data.result) {
                    throw new Error(data.error || 'Failed to load results');
                }
                
                const r = data.result;
                
                // Update titles
                document.getElementById('examTitle').textContent = r.examTitle || 'Exam Results';
                document.getElementById('courseTitle').textContent = r.courseTitle || '';
                
                // Update score card
                const remarkEmoji = r.scorePercent >= 80 ? '🎉' : (r.scorePercent >= 50 ? '👍' : '📚');
                document.getElementById('scoreCard').innerHTML = `
                    <div class="score-text">Your Score</div>
                    <div class="score-value">${r.scorePercent}%</div>
                    <div class="score-text">${remarkEmoji} ${esc(r.remark)}</div>
                    <div class="score-stats">
                        <div class="stat-item">
                            <strong>${r.correct}</strong>
                            Correct
                        </div>
                        <div class="stat-item">
                            <strong>${r.incorrect}</strong>
                            Incorrect
                        </div>
                        <div class="stat-item">
                            <strong>${r.total}</strong>
                            Total
                        </div>
                    </div>
                `;
                
                // Render questions
                if (r.questions && r.questions.length > 0) {
                    document.getElementById('questionsTitle').style.display = 'block';
                    document.getElementById('questionList').innerHTML = r.questions.map((q, idx) => {
                        const isCorrect = q.isCorrect;
                        const statusClass = isCorrect ? 'status-correct' : 'status-incorrect';
                        const statusText = isCorrect ? '✓ Correct' : '✗ Incorrect';
                        
                        const optionsHtml = q.options.map(opt => {
                            let cls = 'answer-choice';
                            if (opt.isCorrect) {
                                cls = 'answer-correct';
                            } else if (opt.isChosen && !opt.isCorrect) {
                                cls = 'answer-incorrect';
                            }
                            
                            const icon = opt.isCorrect ? '✓' : (opt.isChosen ? '✗' : '');
                            return `<div class="${cls}">${icon} ${esc(opt.label)}. ${esc(opt.text)}</div>`;
                        }).join('');
                        
                        return `
                            <div class="question-card">
                                <div class="question-header">
                                    <div class="question-number">Question ${idx + 1}</div>
                                    <div class="question-status ${statusClass}">${statusText}</div>
                                </div>
                                <div class="question-text">${esc(q.text)}</div>
                                ${optionsHtml}
                            </div>
                        `;
                    }).join('');
                }
                
            } catch (err) {
                console.error('Error loading results:', err);
                document.getElementById('scoreCard').innerHTML = `
                    <div class="error">
                        <strong>Error Loading Results</strong><br>
                        ${esc(err.message)}
                    </div>
                `;
            }
        }
        
        // Load results when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadResults();
        });
    </script>
</body>
</html>

