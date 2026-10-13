<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

// Statistics
$totalVoters    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='voter'")->fetchColumn();
$totalCandidates = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$totalVotes     = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();

// Elections with per-election stats
$elections = $pdo->query("SELECT * FROM elections ORDER BY start_time DESC")->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <h1>Administrator Dashboard</h1>
    <p class="muted">Manage elections, candidates, and view results.</p>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= (int)$totalVoters ?></h3>
            <p>Registered Voters</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$totalCandidates ?></h3>
            <p>Total Candidates</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$totalVotes ?></h3>
            <p>Total Votes Cast</p>
        </div>
    </div>

    <div class="admin-actions">
        <a href="manage_elections.php" class="btn btn-primary">Manage Elections</a>
        <a href="manage_candidates.php" class="btn btn-secondary">Manage Candidates</a>
        <a href="voters.php" class="btn btn-outline">View Voters</a>
        <a href="admin_profile.php" class="btn btn-outline">👤 My Profile</a>
    </div>

    <h2>Election Overview</h2>
    <?php if (empty($elections)): ?>
        <p>No elections created yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Votes Cast</th>
                    <th>Not Voted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($elections as $el): 
                $status = get_election_status($el);

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE election_id = ?");
                $stmt->execute([$el['id']]);
                $votesCast = (int)$stmt->fetchColumn();

                $notVoted = $totalVoters - $votesCast;
            ?>
                <tr>
                    <td><?= e($el['title']) ?></td>
                    <td><span class="badge badge-<?= $status ?>"><?= status_label($status) ?></span></td>
                    <td><?= e(date('d M Y H:i', strtotime($el['start_time']))) ?></td>
                    <td><?= e(date('d M Y H:i', strtotime($el['end_time']))) ?></td>
                    <td><?= $votesCast ?></td>
                    <td><?= max(0, $notVoted) ?></td>
                    <td>
                        <a href="results.php?election_id=<?= (int)$el['id'] ?>" class="btn btn-outline btn-sm">Results</a>
                        <a href="export_results.php?election_id=<?= (int)$el['id'] ?>" class="btn btn-secondary btn-sm">Export CSV</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>