<?php
declare(strict_types=1);

function json_ok(array $data = [], int $code = 200): void {
  header('Content-Type: application/json; charset=utf-8');
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function json_error(string $message, int $code = 400, array $extra = []): void {
  json_ok(array_merge(['ok' => false, 'error' => $message], $extra), $code);
}
