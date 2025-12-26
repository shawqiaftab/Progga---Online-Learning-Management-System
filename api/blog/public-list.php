<?php
declare(strict_types=1);
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            bp.id,
            bp.title,
            bp.excerpt,
            bp.category,
            bp.views,
            bp.created_at,
            u.name AS author_name
        FROM blog_posts bp
        LEFT JOIN users u ON u.id = bp.author_id
        WHERE bp.status = 'published'
        ORDER BY bp.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $blogs = [];
    foreach ($rows as $r) {
        $blogs[] = [
            'id' => (int)$r['id'],
            'title' => (string)$r['title'],
            'excerpt' => (string)$r['excerpt'],
            'category' => (string)($r['category'] ?? 'General'),
            'views' => (int)($r['views'] ?? 0),
            'authorName' => (string)($r['author_name'] ?? 'Anonymous'),
            'date' => date('M j, Y', strtotime($r['created_at'])),
        ];
    }

    respond(200, ['ok' => true, 'blogs' => $blogs]);

} catch (Throwable $e) {
    error_log('public-list error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error']);
}

