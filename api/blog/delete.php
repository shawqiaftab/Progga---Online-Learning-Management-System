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

if (empty($_SESSION['user_id'])) {
    respond(401, ['ok' => false, 'error' => 'Not logged in']);
}

$authorId = (int)$_SESSION['user_id'];

$postId = 0;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $postId = (int)($_GET['id'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $postId = (int)($body['id'] ?? 0);
} else {
    respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

if ($postId <= 0) {
    respond(400, ['ok' => false, 'error' => 'Invalid post id']);
}

try {
    $stmt = $pdo->prepare("SELECT id, author_id FROM blog_posts WHERE id = ? LIMIT 1");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        respond(404, ['ok' => false, 'error' => 'Post not found']);
    }

    if ((int)$post['author_id'] !== $authorId) {
        respond(403, ['ok' => false, 'error' => 'You do not have permission to delete this post']);
    }

    $del = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $del->execute([$postId]);

    respond(200, ['ok' => true, 'deleted' => $postId]);

} catch (Throwable $e) {
    error_log('blog/delete error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error']);
}

