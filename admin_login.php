<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'config/db.php';

if (is_logged_in() && $_SESSION['role'] === 'admin') {
    redirect('admin_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = 'admin';
            set_flash('success', 'Admin login successful.');
            redirect('admin_dashboard.php');
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}

include 'includes/header.php';
?>

<section class="form-section">
    <h1>Administrator Login</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Admin Username
            <input type="text" name="username" required autofocus>
        </label>

        <label>Password
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn btn-primary">Login as Admin</button>
    </form>

    <p class="muted" style="text-align:center; margin-top:16px;">
        Not an admin? <a href="login.php">Login as voter</a>
    </p>
</section>

<?php include 'includes/footer.php'; ?>