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
    <title>Exam</title>

    <style>
        :root {
            --primary-color: #adca9d;
        }

        body {
            margin: 0;
            font-family: Poppins, Arial, sans-serif;
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

        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            font-family: Georgia, serif;
            text-decoration: none;
        }

        .logout-link {
            text-decoration: none;
            font-weight: 700;
            color: #a03030;
        }

        .exam-container {
            width: 70%;
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .exam-screen {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 40px;
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e5e5;
            gap: 10px;
        }

        .timer {
            background: #f8d7da;
            color: #a03030;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 16px;
        }

        .question-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 10px;
        }

        .question-text {
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 25px;
            color: #333;
        }

        .choices-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .choice-label {
            display: flex;
            align-items: center;
            padding: 14px;
            background: #fafafa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }

        .choice-label:hover {
            border-color: #2e6d3f;
            background: #f5f9f4;
        }

        .choice-input {
            margin-right: 15px;
            transform: scale(1.2);
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 10px;
        }

        .nav-btn {
            padding: 10px 24px;
            background: #2e6d3f;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .nav-btn:hover:not(:disabled) {
            background: #1e522a;
            transform: translateY(-2px);
        }

        .btn-prev {
            background: #f0f0f0;
            color: #333;
        }

        .btn-prev:hover:not(:disabled) {
            background: #e0e0e0;
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .muted {
            color: #777;
            font-size: 14px;
            margin-top: 12px;
        }

        @media (max-width: 968px) {
            .exam-container {
                width: 95%;
            }

            .exam-screen {
                padding: 20px;
            }

            .exam-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="exam-container">
        <div class="exam-screen" id="examScreen">
            <div class="exam-header">
                <div id="questionProgress">Question —</div>
                <div class="timer" id="timer">--:--</div>
            </div>

            <div class="question-card">
                <div class="question-text" id="questionText">Loading question…</div>
                <div class="choices-container" id="choicesContainer"></div>

                <div class="nav-buttons">
                    <button class="nav-btn btn-prev" id="prevBtn" disabled>Previous</button>
                    <button class="nav-btn" id="nextBtn">Next Question</button>
                </div>

                <div id="statusMsg" class="muted"></div>
            </div>
        </div>
    </div>
    <script>
    
        const BASE = location.pathname.includes('/proj/') ? '/proj' : '';
        const API = (p) => `${BASE}${p}`;

        let courseId = 0;
        let difficulty = 'easy';
        let attemptId = null;
        let endsAtIso = null;

        let exam = null;
        let currentIndex = 0;
        let answers = {};

        function getParams() {
            const sp = new URLSearchParams(window.location.search);
            return {
                courseId: Number(sp.get('courseId') || 0),
                difficulty: String(sp.get('difficulty') || 'easy').toLowerCase(),
                attemptId: sp.get('attemptId'),
                endsAt: sp.get('endsAt')
            };
        }

        async function apiGet(url) {
            const res = await fetch(url, {
                credentials: 'include'
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        async function apiPostJson(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body || {})
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        function esc(s) {
            return String(s ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", "&#039;");
        }

        function loadSavedAnswers() {
            try {
                const key = `attempt_${attemptId}_answers`;
                const raw = sessionStorage.getItem(key);
                if (raw) answers = JSON.parse(raw);
            } catch (_) {}
        }

        function persistAnswers() {
            try {
                const key = `attempt_${attemptId}_answers`;
                sessionStorage.setItem(key, JSON.stringify(answers));
            } catch (_) {}
        }

        function renderQuestion() {
            const q = exam.questions[currentIndex];

            document.getElementById('questionProgress').textContent =
                `Question ${currentIndex + 1} of ${exam.questions.length}`;

            document.getElementById('questionText').innerHTML = esc(q.text);

            const selectedChoiceId = answers[q.id] ?? null;

            const choicesEl = document.getElementById('choicesContainer');
            choicesEl.innerHTML = (q.choices || []).map(ch => {
                const checked = (Number(selectedChoiceId) === Number(ch.id)) ? 'checked' : '';
                return `
        <label class="choice-label" data-choice="${esc(ch.id)}">
          <input type="radio" name="answer" value="${esc(ch.id)}" class="choice-input" ${checked}/>
          <span><b>${esc(ch.label)}</b> ${esc(ch.text)}</span>
        </label>
      `;
            }).join('');

            document.querySelectorAll('.choice-label').forEach(lbl => {
                const cid = Number(lbl.getAttribute('data-choice'));
                if (Number(cid) === Number(selectedChoiceId)) {
                    lbl.style.borderColor = '#2e6d3f';
                    lbl.style.backgroundColor = '#eef5ed';
                }

                lbl.addEventListener('click', async () => {
                    document.querySelectorAll('.choice-label').forEach(l => {
                        l.style.borderColor = '#e0e0e0';
                        l.style.backgroundColor = '#fafafa';
                    });

                    lbl.style.borderColor = '#2e6d3f';
                    lbl.style.backgroundColor = '#eef5ed';

                    answers[q.id] = cid;
                    persistAnswers();

                    try {
                        await apiPostJson(API('/api/exams/save-answer.php'), {
                            attemptId: Number(attemptId),
                            questionId: Number(q.id),
                            choiceId: Number(cid)
                        });
                    } catch (e) {
                        document.getElementById('statusMsg').textContent = e.message;
                    }
                });
            });

            document.getElementById('prevBtn').disabled = (currentIndex === 0);
            document.getElementById('nextBtn').textContent =
                (currentIndex === exam.questions.length - 1) ? 'Submit Exam' : 'Next Question';
        }

        function startTimer() {
            const timerEl = document.getElementById('timer');
            const endMs = Date.parse(endsAtIso);

            const tick = async () => {
                const now = Date.now();
                let sec = Math.max(0, Math.floor((endMs - now) / 1000));

                const mm = String(Math.floor(sec / 60)).padStart(2, '0');
                const ss = String(sec % 60).padStart(2, '0');

                timerEl.textContent = `${mm}:${ss}`;

                if (sec <= 0) {
                    clearInterval(intv);
                    alert('Time is up! Submitting your exam...');
                    await submitExam();
                }
            };

            tick();
            const intv = setInterval(tick, 1000);
        }

        async function submitExam() {
            await apiPostJson(API('/api/exams/submit.php'), {
                attemptId: Number(attemptId)
            });


            window.location.href =
                `${BASE}/results.php?attemptId=${encodeURIComponent(attemptId)}&courseId=${encodeURIComponent(courseId)}`;
        }

        async function boot() {
            const p = getParams();

            courseId = p.courseId;
            difficulty = p.difficulty;
            attemptId = p.attemptId;
            endsAtIso = p.endsAt;

            if (!courseId || !attemptId || !endsAtIso) {
                document.getElementById('statusMsg').textContent =
                    'Missing courseId / attemptId / endsAt. Start from the exam portal.';
                return;
            }

            loadSavedAnswers();


            const data = await apiGet(
                API('/api/exams/get.php') +
                `?courseId=${encodeURIComponent(courseId)}&difficulty=${encodeURIComponent(difficulty)}`
            );

            exam = data.exam;

            if (!exam || !Array.isArray(exam.questions) || exam.questions.length === 0) {
                document.getElementById('statusMsg').textContent =
                    'No MCQ found for this course exam. Add questions from teacher panel.';
                return;
            }

            renderQuestion();
            startTimer();

            document.getElementById('prevBtn').addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    renderQuestion();
                }
            });

            document.getElementById('nextBtn').addEventListener('click', async () => {
                if (currentIndex < exam.questions.length - 1) {
                    currentIndex++;
                    renderQuestion();
                } else {
                    if (!confirm('Submit exam now?')) return;
                    await submitExam();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            boot().catch(err => {
                document.getElementById('statusMsg').textContent = err.message || 'Failed to load exam.';
                alert(document.getElementById('statusMsg').textContent);
            });
        });
    </script>

</body>

</html>
