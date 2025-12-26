<?php

declare(strict_types=1);
session_start();

if (empty($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>

<head>
    <title>Manage Course Content</title>

    <style>
        :root {
            --primary-color: #2e6d3f;
        }

        body {
            margin: 0;
            font-family: Poppins, Arial, sans-serif;
            background: #fff;
            color: #333;
        }

        nav {
            background: rgba(245, 240, 232, .98);
            backdrop-filter: blur(10px);
            padding: 1.2rem 5%;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(255, 107, 53, .1);
            border-bottom: 2px solid rgba(255, 107, 53, .1);
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
        }

        .btn {
            padding: .8rem 1.8rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .container {
            width: 70%;
            max-width: 950px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 16px;
        }

        .card h2 {
            margin: 0 0 12px;
            color: var(--primary-color);
        }

        label {
            display: block;
            font-size: 14px;
            margin: 10px 0 6px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
            box-sizing: border-box;
            background: #fff;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #fff;
        }

        .muted {
            color: #777;
            font-size: 14px;
            margin-top: 10px;
        }

        .q-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 14px;
            margin-top: 10px;
        }

        .q-item .top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }

        .danger {
            background: #f8d7da;
            color: #a03030;
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        @media (max-width:968px) {
            .container {
                width: 95%;
            }

            .nav-links {
                display: none;
            }

            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <nav>
        <div class="nav-container">
            <a class="logo-text" href="index.php">প্রজ্ঞা</a>

            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="teacher-dashboard.php">My Dashboard</a></li>
                <li><a href="discounts.php">Discounts</a></li>
            </ul>

            <a class="btn btn-secondary" href="api/auth/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">

        <div class="card">
            <h2>Course selection</h2>
            <label for="courseSelect">Select course</label>
            <select id="courseSelect">
                <option value="">Loading…</option>
            </select>

            <div class="actions">
                <button class="btn btn-primary" id="saveAllBtn" type="button">Save All</button>
            </div>

            <div class="muted" id="status"></div>
            <div class="muted" id="allMsg"></div>
        </div>

        <div class="card">
            <h2>Course video</h2>
            <label for="videoUrl">Video URL (YouTube / Drive link)</label>
            <input id="videoUrl" placeholder="https://..." />
            <div class="actions">
                <button class="btn btn-primary" id="saveVideoBtn" type="button">Save Video</button>
            </div>
            <div class="muted" id="videoMsg"></div>
        </div>

        <div class="card">
            <h2>Exam settings</h2>

            <div class="row">
                <div>
                    <label for="examTitle">Exam title</label>
                    <input id="examTitle" value="Final Exam" />
                </div>
                <div>
                    <label for="durationMinutes">Duration (minutes)</label>
                    <input id="durationMinutes" type="number" value="30" min="1" max="300" />
                </div>
                <div>
                    <label for="passingScore">Passing score (%)</label>
                    <input id="passingScore" type="number" value="50" min="0" max="100" />
                </div>
                <div>
                    <label for="attemptsAllowed">Attempts allowed</label>
                    <input id="attemptsAllowed" type="number" value="1" min="1" max="50" />
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-primary" id="saveExamBtn" type="button">Save Exam Settings</button>
            </div>
            <div class="muted" id="examMsg"></div>
        </div>

        <div class="card">
            <h2>Add MCQ</h2>

            <label for="qText">Question</label>
            <textarea id="qText" placeholder="Write the question..."></textarea>

            <div class="row">
                <div><label for="aText">A</label><input id="aText" placeholder="Choice A" /></div>
                <div><label for="bText">B</label><input id="bText" placeholder="Choice B" /></div>
                <div><label for="cText">C</label><input id="cText" placeholder="Choice C" /></div>
                <div><label for="dText">D</label><input id="dText" placeholder="Choice D" /></div>
                <div>
                    <label for="correctLabel">Correct answer</label>
                    <select id="correctLabel">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>

            <div class="actions">
                <!-- NEW: explicit Save MCQ button -->
                <button class="btn btn-primary" id="saveMcqBtn" type="button">Save MCQ</button>

                <!-- Optional: keep Add Question if you want, it does the same thing -->
                <button class="btn" id="addQuestionBtn" type="button">Add Another (Save + Clear)</button>
            </div>

            <div class="muted" id="qMsg"></div>
        </div>

        <div class="card">
            <h2>Existing questions</h2>
            <div id="questionsList" class="muted">Select a course to load questions.</div>
        </div>

    </div>

    <script>
        const BASE = location.pathname.includes('/proj/') ? '/proj' : '';
        const API = (p) => `${BASE}${p}`;

        const preselectCourseId = Number(new URLSearchParams(window.location.search).get('courseId') || 0);
        let currentCourseId = 0;
        let courseSelectBound = false;

        async function apiGet(url) {
            const res = await fetch(url, {
                credentials: 'include'
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || 'Request failed');
            return json;
        }

        async function apiPost(url, body) {
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

        function setMsg(id, text) {
            document.getElementById(id).textContent = text || '';
        }

        function escapeHtml(s) {
            return String(s ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", "&#039;");
        }

        function renderQuestions(exam) {
            const el = document.getElementById('questionsList');
            if (!exam) {
                el.textContent = 'No exam yet. Save exam settings first.';
                return;
            }
            if (!Array.isArray(exam.questions) || exam.questions.length === 0) {
                el.textContent = 'No questions yet.';
                return;
            }

            el.innerHTML = '';
            exam.questions.forEach((q, idx) => {
                const div = document.createElement('div');
                div.className = 'q-item';

                const top = document.createElement('div');
                top.className = 'top';

                const left = document.createElement('div');
                left.innerHTML =
                    `<div style="font-weight:800;color:#2e6d3f;">Q${idx+1}.</div>
           <div style="margin-top:6px;">${escapeHtml(q.text)}</div>`;

                const right = document.createElement('div');
                const del = document.createElement('button');
                del.className = 'danger';
                del.textContent = 'Delete';
                del.onclick = async () => {
                    if (!confirm('Delete this question?')) return;
                    await apiPost(API('/api/teacher/question-delete.php'), {
                        courseId: currentCourseId,
                        questionId: q.id
                    });
                    await loadExam();
                };

                right.appendChild(del);
                top.appendChild(left);
                top.appendChild(right);

                const list = document.createElement('div');
                list.style.marginTop = '10px';
                (q.choices || []).forEach(ch => {
                    const row = document.createElement('div');
                    row.className = 'muted';
                    row.textContent = `${ch.label}. ${ch.text}${ch.isCorrect ? ' (correct)' : ''}`;
                    list.appendChild(row);
                });

                div.appendChild(top);
                div.appendChild(list);
                el.appendChild(div);
            });
        }

        async function loadCourses() {
            setMsg('status', 'Loading courses...');
            setMsg('allMsg', '');

            const sel = document.getElementById('courseSelect');
            sel.innerHTML = '<option value="">Select a course</option>';

            const data = await apiGet(API('/api/teacher/courses.php'));
            const courses = Array.isArray(data.courses) ? data.courses : [];

            if (courses.length === 0) {
                setMsg('status', 'No courses found for this instructor.');
                currentCourseId = 0;
                return;
            }

            courses.forEach(c => {
                const opt = document.createElement('option');
                opt.value = String(c.id);
                opt.textContent = `${c.id} — ${c.title}`;
                opt.dataset.videoUrl = c.videoUrl || '';
                sel.appendChild(opt);
            });

            if (preselectCourseId > 0) {
                const exists = Array.from(sel.options).some(o => Number(o.value) === preselectCourseId);
                if (exists) sel.value = String(preselectCourseId);
            }
            if (!sel.value) sel.value = String(courses[0].id);

            currentCourseId = Number(sel.value);
            document.getElementById('videoUrl').value = sel.options[sel.selectedIndex].dataset.videoUrl || '';
            setMsg('status', `Selected course ID: ${currentCourseId}`);

            if (!courseSelectBound) {
                sel.addEventListener('change', async () => {
                    if (!sel.value) return;
                    currentCourseId = Number(sel.value);
                    document.getElementById('videoUrl').value = sel.options[sel.selectedIndex].dataset.videoUrl || '';
                    setMsg('status', `Selected course ID: ${currentCourseId}`);
                    await loadExam();
                });
                courseSelectBound = true;
            }

            await loadExam();
        }

        async function loadExam() {
            if (!currentCourseId) return;

            setMsg('examMsg', 'Loading exam...');
            setMsg('qMsg', '');

            const data = await apiGet(API('/api/teacher/exam-get.php') + `?courseId=${encodeURIComponent(currentCourseId)}`);
            const exam = data.exam || null;

            if (exam) {
                document.getElementById('examTitle').value = exam.title || 'Final Exam';
                document.getElementById('durationMinutes').value = exam.durationMinutes ?? 30;
                document.getElementById('passingScore').value = exam.passingScore ?? 50;
                document.getElementById('attemptsAllowed').value = exam.attemptsAllowed ?? 1;
                setMsg('examMsg', '');
            } else {
                setMsg('examMsg', 'No exam yet. Save exam settings first.');
            }

            renderQuestions(exam);
        }

        async function saveVideo() {
            if (!currentCourseId) throw new Error('Select a course first.');
            const url = document.getElementById('videoUrl').value.trim();
            await apiPost(API('/api/teacher/course-video.php'), {
                courseId: currentCourseId,
                videoUrl: url
            });
        }

        async function saveExamSettings() {
            if (!currentCourseId) throw new Error('Select a course first.');
            await apiPost(API('/api/teacher/exam-upsert.php'), {
                courseId: currentCourseId,
                title: document.getElementById('examTitle').value.trim() || 'Final Exam',
                durationMinutes: Number(document.getElementById('durationMinutes').value || 30),
                passingScore: Number(document.getElementById('passingScore').value || 50),
                attemptsAllowed: Number(document.getElementById('attemptsAllowed').value || 1),
            });
        }

        function readMcqForm() {
            const correct = document.getElementById('correctLabel').value;
            const payload = {
                courseId: currentCourseId,
                questionText: document.getElementById('qText').value.trim(),
                choices: [{
                        label: 'A',
                        text: document.getElementById('aText').value.trim(),
                        isCorrect: correct === 'A'
                    },
                    {
                        label: 'B',
                        text: document.getElementById('bText').value.trim(),
                        isCorrect: correct === 'B'
                    },
                    {
                        label: 'C',
                        text: document.getElementById('cText').value.trim(),
                        isCorrect: correct === 'C'
                    },
                    {
                        label: 'D',
                        text: document.getElementById('dText').value.trim(),
                        isCorrect: correct === 'D'
                    },
                ]
            };

            if (!currentCourseId) throw new Error('Select a course first.');
            if (!payload.questionText) throw new Error('Question is required.');
            for (const ch of payload.choices) {
                if (!ch.text) throw new Error(`Choice ${ch.label} is required.`);
            }
            return payload;
        }

        function clearMcqForm() {
            document.getElementById('qText').value = '';
            document.getElementById('aText').value = '';
            document.getElementById('bText').value = '';
            document.getElementById('cText').value = '';
            document.getElementById('dText').value = '';
            document.getElementById('correctLabel').value = 'A';
        }

        async function saveMcq({
            clearAfter
        } = {
            clearAfter: false
        }) {
            setMsg('qMsg', 'Saving MCQ...');
            const payload = readMcqForm();
            await apiPost(API('/api/teacher/question-create.php'), payload);
            setMsg('qMsg', 'MCQ saved.');
            if (clearAfter) clearMcqForm();
            await loadExam();
        }

        document.getElementById('saveVideoBtn').addEventListener('click', async () => {
            try {
                setMsg('videoMsg', 'Saving...');
                await saveVideo();
                setMsg('videoMsg', 'Saved.');
            } catch (e) {
                setMsg('videoMsg', e.message);
            }
        });

        document.getElementById('saveExamBtn').addEventListener('click', async () => {
            try {
                setMsg('examMsg', 'Saving...');
                await saveExamSettings();
                setMsg('examMsg', 'Saved.');
                await loadExam();
            } catch (e) {
                setMsg('examMsg', e.message);
            }
        });

        document.getElementById('saveAllBtn').addEventListener('click', async () => {
            try {
                setMsg('allMsg', 'Saving everything...');
                await saveVideo();
                await saveExamSettings();
                setMsg('allMsg', 'All saved.');
                await loadExam();
            } catch (e) {
                setMsg('allMsg', e.message);
            }
        });

        // NEW: Save MCQ (does NOT clear)
        document.getElementById('saveMcqBtn').addEventListener('click', async () => {
            try {
                await saveMcq({
                    clearAfter: false
                });
            } catch (e) {
                setMsg('qMsg', e.message);
            }
        });

        // Optional: Add Another = Save + Clear
        document.getElementById('addQuestionBtn').addEventListener('click', async () => {
            try {
                await saveMcq({
                    clearAfter: true
                });
            } catch (e) {
                setMsg('qMsg', e.message);
            }
        });

        loadCourses().catch(err => setMsg('status', err.message));
    </script>
</body>

</html>