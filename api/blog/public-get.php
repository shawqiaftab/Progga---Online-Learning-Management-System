<?php
declare(strict_types=1);
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$postId = (int)($_GET['id'] ?? 0);
if ($postId <= 0) {
    respond(400, ['ok' => false, 'error' => 'Invalid post id']);
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            bp.id,
            bp.title,
            bp.content,
            bp.category,
            bp.views,
            bp.created_at,
            u.name AS author_name
        FROM blog_posts bp
        LEFT JOIN users u ON u.id = bp.author_id
        WHERE bp.id = ? AND bp.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        respond(404, ['ok' => false, 'error' => 'Post not found']);
    }

    // Increment view count
    $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$postId]);

    respond(200, [
        'ok' => true,
        'post' => [
            'id' => (int)$post['id'],
            'title' => (string)$post['title'],
            'content' => nl2br(htmlspecialchars((string)$post['content'])),
            'category' => (string)($post['category'] ?? 'General'),
            'views' => (int)($post['views'] ?? 0) + 1,
            'authorName' => (string)($post['author_name'] ?? 'Anonymous'),
            'date' => date('F j, Y', strtotime($post['created_at'])),
        ],
    ]);

} catch (Throwable $e) {
    error_log('public-get error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error']);
}

