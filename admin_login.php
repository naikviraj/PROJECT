<?php
require_once __DIR__ . '/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth.html');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: auth.html?error=missing');
    exit;
}

try {
    $pdo = getPDO();

    // Using your existing `admin_login` table (columns: id, username, password_hash)
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_login WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_start();
        $_SESSION['admin_id'] = $admin['id'];
        header('Location: admin_dashboard.php');
        exit;
    }

    header('Location: auth.html?error=invalid_admin');
    exit;
} catch (Exception $e) {
    error_log('Admin login error: ' . $e->getMessage());
    header('Location: auth.html?error=server');
    exit;
}
