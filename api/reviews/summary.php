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

error_log("REVIEWS SUMMARY API - courseId: $courseId");

if ($courseId <= 0) {
    error_log("REVIEWS SUMMARY API - Invalid courseId");
    respond(400, ['ok' => false, 'error' => 'Invalid courseId']);
}

try {
    $stmt = $pdo->prepare("
        SELECT AVG(rating) AS avgrating, COUNT(*) AS ratingscount 
        FROM coursereviews 
        WHERE courseid = ?
    ");
    $stmt->execute([$courseId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $avg = ($row && $row['avgrating'] !== null) ? (float)$row['avgrating'] : 0.0;
    $cnt = $row ? (int)$row['ratingscount'] : 0;

    error_log("REVIEWS SUMMARY API - avg: $avg, count: $cnt");

    respond(200, [
        'ok' => true,
        'avg' => $avg,
        'ratings' => $cnt,
        'reviews' => $cnt
    ]);

} catch (Throwable $e) {
    error_log("REVIEWS SUMMARY API - ERROR: " . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Server error']);
}

