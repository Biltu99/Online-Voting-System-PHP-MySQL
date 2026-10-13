<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_voter();

$userId = $_SESSION['user_id'];
$electionId = (int)($_GET['election_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$electionId]);
$election = $stmt->fetch();

if (!$election) {
    set_flash('error', 'Election not found.');
    redirect('dashboard.php');
}

$status = get_election_status($election);
if ($status !== 'open') {
    set_flash('error', 'Voting is not open for this election.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND election_id = ?");
$stmt->execute([$userId, $electionId]);
if ($stmt->fetch()) {
    set_flash('error', 'You have already voted in this election.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ?");
$stmt->execute([$electionId]);
$candidates = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid session token.');
        redirect('vote.php?election_id=' . $electionId);
    }

    $candidateId = (int)($_POST['candidate_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT id FROM candidates WHERE id = ? AND election_id = ?");
    $stmt->execute([$candidateId, $electionId]);
    if (!$stmt->fetch()) {
        set_flash('error', 'Invalid candidate selection.');
        redirect('vote.php?election_id=' . $electionId);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND election_id = ? FOR UPDATE");
        $stmt->execute([$userId, $electionId]);
        if ($stmt->fetch()) {
            throw new Exception('You have already voted in this election.');
        }

        $stmt = $pdo->prepare("INSERT INTO votes (election_id, user_id, candidate_id) VALUES (?, ?, ?)");
        $stmt->execute([$electionId, $userId, $candidateId]);

        $pdo->commit();
        set_flash('success', 'Your vote has been recorded successfully!');
        redirect('dashboard.php');
    } catch (Exception $ex) {
        $pdo->rollBack();
        set_flash('error', $ex->getMessage());
        redirect('dashboard.php');
    }
}

include 'includes/header.php';
?>

<section class="section">
    <h1>Vote: <?= e($election['title']) ?></h1>
    <p class="muted">Select one candidate. You can vote only once.</p>

    <?php if (empty($candidates)): ?>
        <p>No candidates available for this election.</p>
    <?php else: ?>
        <form method="post" id="voteForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="candidate_id" id="selectedCandidateId" value="">

            <div class="candidate-grid">
                <?php foreach ($candidates as $c): ?>
                    <div class="candidate-card" data-id="<?= (int)$c['id'] ?>">
                        <div class="symbol">
                            <?php 
                            $logoPath = 'assets/images/parties/' . ($c['logo'] ?? '');
                            $absPath  = __DIR__ . '/' . $logoPath;
                            if (!empty($c['logo']) && is_file($absPath)): ?>
                                <img src="<?= e($logoPath) ?>" 
                                     alt="<?= e($c['party_name']) ?> logo" 
                                     class="party-logo">
                            <?php else: ?>
                                <?= e($c['symbol']) ?>
                            <?php endif; ?>
                        </div>
                        <h3><?= e($c['candidate_name']) ?></h3>
                        <p class="party"><?= e($c['party_name']) ?></p>
                        <button type="button" class="btn btn-outline select-btn">Select</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="confirm-bar">
                <p id="confirmText" class="muted">No candidate selected yet.</p>
                <button type="submit" class="btn btn-primary" id="submitVote" disabled>Confirm & Submit Vote</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>