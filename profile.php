<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_voter();

$userId = $_SESSION['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email     = trim($_POST['email'] ?? '');

            if ($full_name === '') $errors[] = 'Full name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';

            if (empty($errors)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email already in use.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$full_name, $email, $userId]);
                    $_SESSION['full_name'] = $full_name;
                    set_flash('success', 'Profile updated.');
                    redirect('profile.php');
                }
            }
        }

        if ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($new) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($new !== $confirm) {
                $errors[] = 'New passwords do not match.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hash, $userId]);
                set_flash('success', 'Password changed successfully.');
                redirect('profile.php');
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<section class="section">
    <h1>My Profile</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Basic Information</h2>
        <p><strong>Username:</strong> <?= e($user['username']) ?> <em>(cannot be changed)</em></p>

        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_profile">

            <label>Full Name
                <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required>
            </label>

            <label>Email
                <input type="email" name="email" value="<?= e($user['email']) ?>" required>
            </label>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <div class="card">
        <h2>Change Password</h2>
        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_password">

            <label>Current Password
                <input type="password" name="current_password" required>
            </label>

            <label>New Password
                <input type="password" name="new_password" required minlength="6">
            </label>

            <label>Confirm New Password
                <input type="password" name="confirm_password" required minlength="6">
            </label>

            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>