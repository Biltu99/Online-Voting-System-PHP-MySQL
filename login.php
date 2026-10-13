<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';      // <-- ADDED
require_once 'config/db.php';

if (is_logged_in()) {
    redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter username and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'voter'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                redirect('dashboard.php');
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="form-section">
    <h1>Voter Login</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Username
            <input type="text" name="username" required>
        </label>

        <label>Password
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn btn-primary">Login</button>
        <p class="muted">New voter? <a href="register.php">Register here</a></p>
    </form>
</section>

<?php include 'includes/footer.php'; ?>