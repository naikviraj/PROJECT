<?php
require_once __DIR__ . '/conn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pets.php'); exit;
}

if (empty($_SESSION['user_id'])) {
    header('Location: auth.html'); exit;
}

$pet_id = (int)($_POST['pet_id'] ?? 0);
if ($pet_id <= 0) { header('Location: pets.php'); exit; }

try {
    $pdo = getPDO();
    // check pet exists and not already adopted
    $stmt = $pdo->prepare('SELECT id, is_adopted FROM pets WHERE id = ? LIMIT 1');
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch();
    if (!$pet || $pet['is_adopted']) {
        header('Location: pets.php?error=not_available'); exit;
    }

    // mark adopted and record adoption
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE pets SET is_adopted = 1 WHERE id = ?');
    $stmt->execute([$pet_id]);
    $stmt = $pdo->prepare('INSERT INTO adoptions (pet_id, user_id, adopted_at) VALUES (?, ?, NOW())');
    $stmt->execute([$pet_id, $_SESSION['user_id']]);
    $pdo->commit();
    // Set a pet-specific flash message for the pets page (so we can show it inline)
    // $_SESSION['flash_success'] = 'Adoption request sent successfully.';
    $_SESSION['flash_pet_id'] = $pet_id;
    // Track recently adopted pets in this session so their cards remain visible
    if (!isset($_SESSION['recent_adopted']) || !is_array($_SESSION['recent_adopted'])) {
        $_SESSION['recent_adopted'] = [];
    }
    if (!in_array($pet_id, $_SESSION['recent_adopted'], true)) {
        $_SESSION['recent_adopted'][] = $pet_id;
    }

    header('Location: pets.php'); exit;
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Adopt error: ' . $e->getMessage());
    header('Location: pets.php?error=server'); exit;
}
