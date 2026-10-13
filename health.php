<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

$checks = [];

// 1. DB connection
$checks['Database Connection'] = $pdo ? 'OK' : 'FAIL';

// 2. Tables exist
foreach (['users', 'elections', 'candidates', 'votes'] as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $checks["Table: $table"] = "OK ($count rows)";
    } catch (Exception $e) {
        $checks["Table: $table"] = 'MISSING';
    }
}

// 3. Unique constraint on votes
try {
    $stmt = $pdo->query("SHOW INDEX FROM votes WHERE Key_name = 'unique_vote'");
    $checks['Unique constraint on votes(election_id, user_id)'] = $stmt->fetch() ? 'OK' : 'MISSING';
} catch (Exception $e) {
    $checks['Unique constraint on votes'] = 'ERROR';
}

// 4. PHP version
$checks['PHP Version'] = PHP_VERSION;

// 5. Sessions
$checks['Sessions'] = session_status() === PHP_SESSION_ACTIVE ? 'OK' : 'INACTIVE';

// 6. Password hashing
$checks['password_hash() available'] = function_exists('password_hash') ? 'OK' : 'MISSING';

include 'includes/header.php';
?>

<section class="section">
    <h1>🩺 System Health Check</h1>
    <p class="muted">Quick verification of the environment for demonstration.</p>

    <table class="data-table">
        <thead>
            <tr><th>Check</th><th>Result</th></tr>
        </thead>
        <tbody>
            <?php foreach ($checks as $label => $result): 
                $ok = str_starts_with($result, 'OK');
            ?>
                <tr>
                    <td><?= e($label) ?></td>
                    <td>
                        <span class="badge badge-<?= $ok ? 'open' : 'ended' ?>">
                            <?= e($result) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:20px">
        <a href="index.php" class="btn btn-primary">← Back to Home</a>
    </p>
</section>

<?php include 'includes/footer.php'; ?>