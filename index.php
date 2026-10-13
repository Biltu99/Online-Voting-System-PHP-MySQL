<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

$stmt = $pdo->query("SELECT * FROM elections ORDER BY start_time DESC");
$elections = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="hero">
    <h1>Online Voting System</h1>
    <p class="lead">
        A simple and transparent platform for conducting college elections online. Register as a voter, cast your vote once, and view the election results when voting ends.
    </p>
    <div class="notice">
        📘 <strong>College Assignment Project:</strong> This Online Voting System is developed for academic purposes as part of a college assignment.
    </div>
    <div class="hero-actions">
        <a href="register.php" class="btn btn-primary">Register as Voter</a>
        <a href="login.php" class="btn btn-secondary">Voter Login</a>
        <a href="results.php" class="btn btn-outline">View Results</a>
    </div>
</section>

<section class="section">
    <h2>Available Elections</h2>
    <?php if (empty($elections)): ?>
        <p>No elections have been created yet.</p>
    <?php else: ?>
        <div class="election-list">
            <?php foreach ($elections as $el): 
                $status = get_election_status($el); ?>
                <div class="election-card">
                    <h3><?= e($el['title']) ?></h3>
                    <p><?= e($el['description']) ?></p>
                    <p class="meta">
                        <span class="badge badge-<?= $status ?>"><?= status_label($status) ?></span>
                        <small>Ends: <?= e(date('d M Y, H:i', strtotime($el['end_time']))) ?>
    <?php if ($status === 'open'): ?>
        · <span class="countdown" data-countdown="<?= e($el['end_time']) ?>">calculating...</span>
    <?php endif; ?>
</small>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>