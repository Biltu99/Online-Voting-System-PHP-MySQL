<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT id, full_name, username, email, created_at
        FROM users
        WHERE role='voter'
          AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?)
        ORDER BY created_at DESC
    ");
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
    $voters = $stmt->fetchAll();
} else {
    $voters = $pdo->query("
        SELECT id, full_name, username, email, created_at
        FROM users WHERE role='voter' ORDER BY created_at DESC
    ")->fetchAll();
}

include 'includes/header.php';
?>

<section class="section">
    <h1>Registered Voters</h1>
    <p class="muted">Total: <strong><?= count($voters) ?></strong> voter(s) found.</p>

    <form method="get" class="search-bar">
        <input type="text" name="q" placeholder="Search by name, username, or email..."
               value="<?= e($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search): ?>
            <a href="voters.php" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($voters)): ?>
        <p>No voters found.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($voters as $i => $v): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($v['full_name']) ?></td>
                        <td><?= e($v['username']) ?></td>
                        <td><?= e($v['email']) ?></td>
                        <td><?= e(date('d M Y H:i', strtotime($v['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>