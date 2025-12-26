<?php
declare(strict_types=1);

session_start(); 

require __DIR__ . '/../../config/db.php'; 

function respond(int $code, array $data): void {
  header('Content-Type: application/json; charset=utf-8');
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(405, ["ok" => false, "error" => "Method not allowed"]);
}


$raw = file_get_contents("php://input");
$data = json_decode($raw, true) ?? [];

$name = trim((string)($data["name"] ?? ""));
$email = trim((string)($data["email"] ?? ""));
$role = trim((string)($data["role"] ?? "learner"));
$pass = (string)($data["password"] ?? "");
$pass2 = (string)($data["confirm_password"] ?? $pass);

if ($name === "" || $email === "" || $pass === "") {
  respond(400, ["ok" => false, "error" => "Name, email and password required"]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(400, ["ok" => false, "error" => "Invalid email address"]);
}

if ($pass !== $pass2) {
  respond(400, ["ok" => false, "error" => "Passwords do not match"]);
}

if (!in_array($role, ["learner", "teacher"], true)) {
  $role = "learner";
}


$chk = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$chk->execute([$email]);
if ($chk->fetch()) {
  respond(409, ["ok" => false, "error" => "Email already registered"]);
}


$hash = password_hash($pass, PASSWORD_DEFAULT);
$ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
$ins->execute([$name, $email, $hash, $role]);

$userId = (int)$pdo->lastInsertId();


$_SESSION["user_id"] = $userId;
$_SESSION["name"] = $name;
$_SESSION["email"] = $email;
$_SESSION["role"] = $role;

$redirect = ($role === "teacher")
  ? "/proj/teacher-dashboard.php"
  : "/proj/student-dashboard.php";

respond(201, [
  "ok" => true,
  "user" => [
    "id" => $userId,
    "name" => $name,
    "email" => $email,
    "role" => $role,
  ],
  "redirect" => $redirect
]);
