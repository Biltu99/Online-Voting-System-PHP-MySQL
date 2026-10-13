<?php
// ============================================================
// setup_admin.php
// Run ONCE to create the admin account, then DELETE this file.
// Admin username: Biltu99
// Admin password: Biltu9900
// ============================================================

require_once 'config/db.php';

$full_name = 'Biltu Admin';
$username  = 'Biltu99';
$email     = 'biltu99@admin.com';
$password  = 'Biltu9900';
$role      = 'admin';

// Generate a fresh bcrypt hash from the plain password
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if the username or email already exists
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        // If it exists, update it to make sure it's admin with the new password
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, password_hash = ?, role = 'admin'
            WHERE id = ?
        ");
        $stmt->execute([$full_name, $email, $hash, $existing['id']]);
        echo "<h2 style='color:green'>✅ Admin account UPDATED successfully!</h2>";
    } else {
        // Insert new admin
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, username, email, password_hash, role)
            VALUES (?, ?, ?, ?, 'admin')
        ");
        $stmt->execute([$full_name, $username, $email, $hash]);
        echo "<h2 style='color:green'>✅ Admin account CREATED successfully!</h2>";
    }

    echo "<hr>";
    echo "<p><strong>Username:</strong> Biltu99</p>";
    echo "<p><strong>Password:</strong> Biltu9900</p>";
    echo "<p><strong>Role:</strong> admin</p>";
    echo "<p><strong>Password Hash:</strong> <code>" . htmlspecialchars($hash) . "</code></p>";
    echo "<hr>";
    echo "<p>👉 Now <a href='admin_login.php'>go to Admin Login</a> and log in.</p>";
    echo "<p style='color:red'><strong>⚠️ IMPORTANT: Delete this file (<code>setup_admin.php</code>) after use!</strong></p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}