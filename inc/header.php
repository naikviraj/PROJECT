<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Helper variables for templates
$isUser = !empty($_SESSION['user_id']);
$isAdmin = !empty($_SESSION['admin_id']);

if (!function_exists('navLink')) {
    function navLink($href, $text) {
        return "<li><a href=\"$href\">$text</a></li>";
    }
}
?>
<header class="nav-bar">
    <ul>
        <li class="b">Pawfect Match</li>
        <div class="nav-links">
            <?= navLink('Page1.php', 'Home') ?>
            <?= navLink('aboutus.html', 'About Us') ?>
            <?= navLink('Vol.html', 'Volunteering') ?>
            <?= navLink('Support.html', 'Support') ?>
            <?= navLink('pets.php', 'Pets') ?>
            <?php if ($isUser): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php elseif ($isAdmin): ?>
                <li><a href="admin_dashboard.php">Admin</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <?= navLink('auth.html', 'Login / Register') ?>
            <?php endif; ?>
        </div>
    </ul>
</header>
