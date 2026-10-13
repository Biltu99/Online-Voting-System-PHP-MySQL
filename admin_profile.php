<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$userId = $_SESSION['user_id'];
$errors = [];

// ============================================================
// Handle POST (update profile / change password)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token.';
    } else {
        $action = $_POST['action'] ?? '';

        // --------------------------------------------------------
        // Update basic info (full name + email — username is LOCKED)
        // --------------------------------------------------------
        if ($action === 'update_profile') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email     = trim($_POST['email'] ?? '');

            if ($full_name === '') $errors[] = 'Full name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }

            if (empty($errors)) {
                // Check email uniqueness (excluding self)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $errors[] = 'That email is already in use by another account.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$full_name, $email, $userId]);
                    $_SESSION['full_name'] = $full_name;
                    set_flash('success', 'Profile updated successfully.');
                    redirect('admin_profile.php');
                }
            }
        }

        // --------------------------------------------------------
        // Change password
        // --------------------------------------------------------
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
                redirect('admin_profile.php');
            }
        }
    }
}

// ============================================================
// Fetch current admin user
// ============================================================
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$admin = $stmt->fetch();

include 'includes/header.php';
?>

<section class="section">
    <h1>👤 My Admin Profile</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
        </div>
    <?php endif; ?>

    <!-- ============================================================
         Account Details Card
         ============================================================ -->
    <div class="card admin-details-card">
        <h2>Account Details</h2>

        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Full Name</span>
                <span class="detail-value"><?= e($admin['full_name']) ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Username</span>
                <span class="detail-value">
                    <?= e($admin['username']) ?>
                    <span class="badge badge-not_started" title="Username cannot be changed">🔒 Locked</span>
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= e($admin['email']) ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Role</span>
                <span class="detail-value">
                    <span class="badge badge-open">🛡️ Administrator</span>
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">User ID</span>
                <span class="detail-value">#<?= (int)$admin['id'] ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Joined</span>
                <span class="detail-value">
                    <?= e(date('d M Y, H:i', strtotime($admin['created_at']))) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ============================================================
         Update Basic Info Form
         ============================================================ -->
    <div class="card">
        <h2>Update Basic Information</h2>

        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_profile">

            <label>Username (cannot be changed)
                <input type="text" 
                       value="<?= e($admin['username']) ?>" 
                       disabled 
                       class="locked-input"
                       title="Username is permanently locked">
            </label>

            <label>Full Name
                <input type="text" 
                       name="full_name" 
                       value="<?= e($admin['full_name']) ?>" 
                       required>
            </label>

            <label>Email
                <input type="email" 
                       name="email" 
                       value="<?= e($admin['email']) ?>" 
                       required>
            </label>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <!-- ============================================================
         Change Password Form
         ============================================================ -->
    <div class="card">
        <h2>🔒 Change Password</h2>

        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_password">

            <label>Current Password
                <input type="password" name="current_password" required>
            </label>

            <label>New Password
                <input type="password" 
                       name="new_password" 
                       required 
                       minlength="6"
                       id="newPasswordInput">
                <div class="strength-meter">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <small id="strengthText" class="muted">Enter a new password</small>
            </label>

            <label>Confirm New Password
                <input type="password" 
                       name="confirm_password" 
                       required 
                       minlength="6">
            </label>

            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    <!-- ============================================================
         Quick Actions
         ============================================================ -->
    <div class="card">
        <h2>Quick Actions</h2>
        <div class="admin-actions">
            <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            <a href="manage_elections.php" class="btn btn-outline">Manage Elections</a>
            <a href="manage_candidates.php" class="btn btn-outline">Manage Candidates</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>