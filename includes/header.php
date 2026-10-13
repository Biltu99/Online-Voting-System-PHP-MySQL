<?php
// includes/header.php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$flash = get_flash();

// Decide where the "Home" link should point based on role
$homeLink = 'index.php';
if (is_logged_in()) {
    $homeLink = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'dashboard.php';
}

// Get current page filename to mark active link
$currentPage = basename($_SERVER['PHP_SELF']);

function nav_active($page, $currentPage) {
    return $page === $currentPage ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System - College Assignment</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= e($homeLink) ?>" class="brand">🗳️ Online Voting System</a>
        <nav class="nav">
            <a href="<?= e($homeLink) ?>" class="nav-link<?= nav_active(basename($homeLink), $currentPage) ?>">Home</a>
            <a href="results.php" class="nav-link<?= nav_active('results.php', $currentPage) ?>">Results</a>
            <?php if (is_logged_in() && $_SESSION['role'] === 'voter'): ?>
                <!-- Voter nav -->
                <a href="profile.php" class="nav-link<?= nav_active('profile.php', $currentPage) ?>">Profile</a>
                <a href="logout.php" class="btn-link">Logout</a>
            <?php elseif (is_logged_in() && $_SESSION['role'] === 'admin'): ?>
                <!-- Admin nav — no redundant "Admin" link -->
                <a href="admin_profile.php" class="nav-link<?= nav_active('admin_profile.php', $currentPage) ?>">Profile</a>
                <a href="logout.php" class="btn-link">Logout</a>
            <?php else: ?>
                <!-- Guest nav -->
                <a href="login.php" class="nav-link<?= nav_active('login.php', $currentPage) ?>">Voter Login</a>
                <a href="register.php" class="nav-link<?= nav_active('register.php', $currentPage) ?>">Register</a>
                <a href="admin_login.php" class="nav-link<?= nav_active('admin_login.php', $currentPage) ?>">Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>