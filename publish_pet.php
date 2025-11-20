<?php
require_once __DIR__ . '/conn.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) { header('Location: auth.html'); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admin_dashboard.php'); exit; }

try {
    $pdo = getPDO();
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? 'dog');
    $breed = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || !isset($_FILES['image'])) {
        header('Location: admin_dashboard.php?error=missing'); exit;
    }

    // handle image upload
    $uploadDir = 'IMAGES';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $file = $_FILES['image'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload error');
    if (!in_array(strtolower($ext), $allowed)) throw new Exception('Invalid image type');
    $filename = uniqid('pet_', true) . '.' . $ext;
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $target)) throw new Exception('Failed to save image');

    $stmt = $pdo->prepare('INSERT INTO pets (admin_id, name, `type`, breed, age, description, image_path, is_published, created_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
    $stmt->execute([$_SESSION['admin_id'], $name, $type, $breed, $age, $description, $target]);

    header('Location: admin_dashboard.php?published=1'); exit;
} catch (Exception $e) {
    error_log('Publish error: ' . $e->getMessage());
    header('Location: admin_dashboard.php?error=server'); exit;
}
