<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// clear both user and admin sessions
unset($_SESSION['user_id'], $_SESSION['admin_id']);
session_destroy();
header('Location: Page1.php'); exit;
