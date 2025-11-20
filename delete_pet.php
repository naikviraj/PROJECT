<?php
require_once __DIR__ . '/conn.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) { header('Location: auth.html'); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: admin_dashboard.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT image_path FROM pets WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch();
if ($row) {
    if (!empty($row['image_path']) && file_exists(__DIR__ . '/' . $row['image_path'])) @unlink(__DIR__ . '/' . $row['image_path']);
    $stmt = $pdo->prepare('DELETE FROM pets WHERE id = ?'); $stmt->execute([$id]);
}
header('Location: admin_dashboard.php'); exit;
