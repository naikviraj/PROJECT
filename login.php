<?php
require_once __DIR__ . '/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: auth.html?error=missing');
    exit;
}

try {
    $pdo = getPDO();
    // Using table `user_register` which holds registered users
    $stmt = $pdo->prepare('SELECT id, password_hash FROM user_register WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        header('Location: Page1.html');
        exit;
    }

    header('Location: auth.html?error=invalid');
    exit;
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    header('Location: auth.html?error=server');
    exit;
}
