<?php
require_once __DIR__ . '/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if ($name === '' || $email === '' || $password === '') {
    header('Location: auth.html?error=missing');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: auth.html?error=invalid_email');
    exit;
}
if (strlen($name) > 255) {
    header('Location: auth.html?error=name_too_long');
    exit;
}
// stronger server-side password policy: at least 8 chars, upper, lower, digit
if (strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password)) {
    header('Location: auth.html?error=weak_password');
    exit;
}

try {
    $pdo = getPDO();

    // Check if email already exists (table: user_register)
    $stmt = $pdo->prepare('SELECT id FROM user_register WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: auth.html?error=exists');
        exit;
    }

    // Hash password and insert
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO user_register (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $hash]);

    // Do NOT auto-login the user. Redirect to login page so they can sign in.
    header('Location: auth.html?registered=1');
    exit;
} catch (Exception $e) {
    error_log('Register error: ' . $e->getMessage());
    header('Location: auth.html?error=server');
    exit;
}
