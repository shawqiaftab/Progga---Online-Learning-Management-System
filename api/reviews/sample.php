<?php
declare(strict_types=1);
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$courseId = (int)($_GET['courseId'] ?? 0);

error_log("REVIEWS SAMPLE API - courseId: $courseId");

if ($courseId <= 0) {
    error_log("REVIEWS SAMPLE API - Invalid courseId");
    respond(400, ['ok' => false, 'error' => 'Invalid courseId']);
}

try {
    $stmt = $pdo->prepare("
        SELECT u.name, r.rating, r.comment 
        FROM coursereviews r
        LEFT JOIN users u ON u.id = r.userid
        WHERE r.courseid = ?
        ORDER BY r.createdat DESC
        LIMIT 3
    ");
    $stmt->execute([$courseId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("REVIEWS SAMPLE API - Found " . count($rows) . " reviews");

    $reviews = array_map(fn($r) => [
        'name' => (string)($r['name'] ?: 'Student'),
        'rating' => (int)($r['rating'] ?? 0),
        'comment' => (string)($r['comment'] ?? ''),
    ], $rows);

    respond(200, ['ok' => true, 'reviews' => $reviews]);

} catch (Throwable $e) {
    error_log("REVIEWS SAMPLE API - ERROR: " . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error']);
}

