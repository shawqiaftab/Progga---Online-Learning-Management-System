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

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            excerpt,
            status,
            views,
            created_at,
            COALESCE(updated_at, created_at) AS updated_at
        FROM blog_posts
        WHERE author_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$authorId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $published = [];
    $drafts    = [];
    $totalViews = 0;

    foreach ($rows as $r) {
        $post = [
            'id'        => (int)$r['id'],
            'title'     => (string)$r['title'],
            'excerpt'   => (string)$r['excerpt'],
            'status'    => (string)$r['status'],
            'views'     => (int)($r['views'] ?? 0),
            'createdAt' => (string)$r['created_at'],
            'updatedAt' => (string)$r['updated_at'],
        ];

        $totalViews += $post['views'];

        if ($post['status'] === 'published') {
            $published[] = $post;
        } else {
            $drafts[] = $post;
        }
    }

    respond(200, [
        'ok' => true,
        'published' => $published,
        'drafts' => $drafts,
        'stats' => [
            'totalPosts'  => count($rows),
            'published'   => count($published),
            'drafts'      => count($drafts),
            'totalViews'  => $totalViews,
        ],
    ]);

} catch (Throwable $e) {
    error_log('blog/list error: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Database error']);
}

