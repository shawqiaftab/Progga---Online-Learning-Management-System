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
$data = json_decode($raw, true);

$email = trim((string)($data["email"] ?? ""));
$pass = (string)($data["password"] ?? "");

if ($email === "" || $pass === "") {
  respond(400, ["ok" => false, "error" => "Email and password required"]);
}


$stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
  respond(401, ["ok" => false, "error" => "Invalid email or password"]);
}


if (!password_verify($pass, (string)$user["password_hash"])) {
  respond(401, ["ok" => false, "error" => "Invalid email or password"]);
}


$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["name"] = (string)$user["name"];
$_SESSION["email"] = (string)$user["email"];
$_SESSION["role"] = (string)$user["role"];


$role = $_SESSION["role"];
$redirect = ($role === "teacher" || $role === "admin")
  ? "/proj/teacher-dashboard.php"
  : "/proj/student-dashboard.php";

respond(200, [
  "ok" => true,
  "user" => [
    "id" => (int)$user["id"],
    "name" => (string)$user["name"],
    "email" => (string)$user["email"],
    "role" => (string)$user["role"],
  ],
  "redirect" => $redirect
]);
