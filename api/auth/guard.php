<?php

declare(strict_types=1);


if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start(); 
}


const BASE_PATH = "/proj";

function redirect(string $path): void {
  header("Location: " . BASE_PATH . $path);
  exit; 
}

function require_login(): void {
  if (empty($_SESSION["user_id"])) {
    redirect("/login.html");
  }
}

function require_role(string $role): void {
  require_login();
  if (($_SESSION["role"] ?? "") !== $role) {
    http_response_code(403);
    echo "Forbidden";
    exit;
  }
}

function current_user_id(): int {
  return (int)($_SESSION["user_id"] ?? 0);
}

function current_user_name(): string {
  return (string)($_SESSION["name"] ?? "");
}

function current_user_email(): string {
  return (string)($_SESSION["email"] ?? "");
}

function current_user_role(): string {
  return (string)($_SESSION["role"] ?? "");
}
