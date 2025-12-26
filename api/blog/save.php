<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

if (empty($_SESSION['user_id'])) {
    respond(401, ['ok' => false, 'error' => 'Not logged in']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    respond(400, ['ok' => false, 'error' => 'Invalid JSON']);
}

$authorId = (int)$_SESSION['user_id'];
$id       = isset($data['id']) ? (int)$data['id'] : 0;
$title    = trim((string)($data['title'] ?? ''));
$category = trim((string)($data['category'] ?? 'Learning Journey'));
$status   = trim((string)($data['status'] ?? 'draft'));
$content  = (string)($data['content'] ?? '');

if ($title === '' || strlen($title) > 255) {
    respond(400, ['ok' => false, 'error' => 'Title is required and must be <= 255 characters']);
}
if (!in_array($status, ['draft', 'published'], true)) {
    respond(400, ['ok' => false, 'error' => 'Invalid status']);
}

$excerpt = mb_substr(strip_tags($content), 0, 200);

try {
    if ($id > 0) {
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE blog_posts
            SET title = ?, excerpt = ?, content = ?, status = ?, category = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND author_id = ?
        ");
        $stmt->execute([$title, $excerpt, $content, $status, $category, $id, $authorId]);
    } else {
        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO blog_posts (author_id, title, excerpt, content, status, category)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$authorId, $title, $excerpt, $content, $status, $category]);
        $id = (int)$pdo->lastInsertId();
    }

    respond(200, [
        'ok' => true,
        'id' => $id,
        'message' => 'Post saved successfully',
    ]);

} catch (Throwable $e) {
    error_log('blog/save error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Database error']);
}

