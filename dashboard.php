<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_voter();

$userId = $_SESSION['user_id'];

// ---- Fetch full voter details ----
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$me = $stmt->fetch();

// ---- Elections list ----
$elections = $pdo->query("SELECT * FROM elections ORDER BY start_time DESC")->fetchAll();

// ---- Which elections has this voter already voted in? ----
$stmt = $pdo->prepare("SELECT election_id, candidate_id FROM votes WHERE user_id = ?");
$stmt->execute([$userId]);
$myVotes = [];
foreach ($stmt->fetchAll() as $row) {
    $myVotes[$row['election_id']] = $row['candidate_id'];
}

// ---- Count votes cast by this voter ----
$votesCast = count($myVotes);
$totalElections = count($elections);

include 'includes/header.php';
?>

<section class="section">
    <h1>Welcome, <?= e($me['full_name']) ?> 👋</h1>
    <p class="muted">Here is your voter profile and available elections.</p>

    <!-- ============================================================
         VOTER PROFILE CARD
         ============================================================ -->
    <div class="card profile-hero">
        <div class="profile-hero-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($me['full_name'], 0, 1)) ?>
            </div>
            <div class="profile-hero-info">
                <h2><?= e($me['full_name']) ?></h2>
                <p class="muted">
                    <span class="badge badge-open">🗳️ Registered Voter</span>
                    <span class="badge badge-not_started">🪪 <?= e($me['voter_epic_no'] ?? 'EPIC not set') ?></span>
                </p>
            </div>
            <div class="profile-hero-stats">
                <div class="mini-stat">
                    <strong><?= (int)$votesCast ?></strong>
                    <span>Votes Cast</span>
                </div>
                <div class="mini-stat">
                    <strong><?= (int)$totalElections ?></strong>
                    <span>Elections</span>
                </div>
            </div>
        </div>

        <!-- ============ DETAIL GRID ============ -->
        <div class="detail-grid" style="margin-top:22px;">

            <div class="detail-item">
                <span class="detail-label">Username</span>
                <span class="detail-value"><?= e($me['username']) ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= e($me['email']) ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Voter EPIC Number</span>
                <span class="detail-value"><?= e($me['voter_epic_no'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Date of Birth</span>
                <span class="detail-value">
                    <?= !empty($me['date_of_birth']) 
                        ? e(date('d M Y', strtotime($me['date_of_birth']))) 
                        : '—' ?>
                    <?php if (!empty($me['date_of_birth'])): 
                        $age = (new DateTime($me['date_of_birth']))->diff(new DateTime())->y;
                    ?>
                        <small class="muted">(<?= $age ?> yrs)</small>
                    <?php endif; ?>
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Gender</span>
                <span class="detail-value"><?= e($me['gender'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Mobile Number</span>
                <span class="detail-value"><?= e($me['mobile'] ?? '—') ?></span>
            </div>

            <div class="detail-item span-2">
                <span class="detail-label">Address</span>
                <span class="detail-value"><?= e($me['address_line'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">State</span>
                <span class="detail-value"><?= e($me['state'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">District</span>
                <span class="detail-value"><?= e($me['district'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">PIN Code</span>
                <span class="detail-value"><?= e($me['pincode'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Ward Number</span>
                <span class="detail-value"><?= e($me['ward_number'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Assembly Constituency</span>
                <span class="detail-value"><?= e($me['assembly_constituency'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Parliamentary Constituency</span>
                <span class="detail-value"><?= e($me['parliamentary_constituency'] ?? '—') ?></span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Registered On</span>
                <span class="detail-value">
                    <?= e(date('d M Y, H:i', strtotime($me['created_at']))) ?>
                </span>
            </div>

        </div>

        <div class="profile-hero-actions">
            <a href="profile.php" class="btn btn-outline">✏️ Edit Profile</a>
        </div>
    </div>

    <!-- ============================================================
         ELECTIONS
         ============================================================ -->
    <h2 style="margin-top:35px;">🗳️ Available Elections</h2>

    <?php if (empty($elections)): ?>
        <p>No elections available at the moment.</p>
    <?php else: ?>
        <?php foreach ($elections as $el): 
            $status = get_election_status($el);
            $hasVoted = isset($myVotes[$el['id']]);
        ?>
        <div class="card election-block">
            <h2><?= e($el['title']) ?></h2>
            <p><?= e($el['description']) ?></p>
            <p>
                <span class="badge badge-<?= $status ?>"><?= status_label($status) ?></span>
                <small>Ends: <?= e(date('d M Y, H:i', strtotime($el['end_time']))) ?></small>
            </p>

            <?php if ($hasVoted): ?>
                <div class="alert alert-success">✅ You have already voted in this election.</div>
                <?php if ($status === 'ended'): ?>
                    <a href="results.php?election_id=<?= (int)$el['id'] ?>" class="btn btn-outline">View Results</a>
                <?php endif; ?>
            <?php elseif ($status === 'open'): ?>
                <a href="vote.php?election_id=<?= (int)$el['id'] ?>" class="btn btn-primary">Vote Now</a>
            <?php elseif ($status === 'not_started'): ?>
                <div class="alert alert-info">Voting has not started yet.</div>
            <?php else: ?>
                <div class="alert alert-info">Voting has ended.</div>
                <a href="results.php?election_id=<?= (int)$el['id'] ?>" class="btn btn-outline">View Results</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>