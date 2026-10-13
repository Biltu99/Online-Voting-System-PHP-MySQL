<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

$electionId = (int)($_GET['election_id'] ?? 0);

// ============================================================
// No election selected → show list of elections
// ============================================================
if (!$electionId) {
    $elections = $pdo->query("SELECT * FROM elections ORDER BY start_time DESC")->fetchAll();
    include 'includes/header.php';
    ?>
    <section class="section">
        <h1>Election Results</h1>
        <p class="muted">Select an election to view its result.</p>
        <div class="election-list">
            <?php foreach ($elections as $el): 
                $status = get_election_status($el); ?>
                <div class="election-card">
                    <h3><?= e($el['title']) ?></h3>
                    <p><span class="badge badge-<?= $status ?>"><?= status_label($status) ?></span></p>
                    <a href="results.php?election_id=<?= (int)$el['id'] ?>" class="btn btn-outline">View</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

// ============================================================
// Load selected election
// ============================================================
$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$electionId]);
$election = $stmt->fetch();

if (!$election) {
    set_flash('error', 'Election not found.');
    redirect('results.php');
}

$status = get_election_status($election);

include 'includes/header.php';
?>

<section class="section">
    <h1>Results: <?= e($election['title']) ?></h1>
    <p><span class="badge badge-<?= $status ?>"><?= status_label($status) ?></span></p>

    <?php if ($status !== 'ended'): ?>
        <div class="alert alert-info">
            🔒 Results will be visible only after the election ends.
        </div>
    <?php else: 
        // ---- Fetch candidate-wise vote counts (including logo) ----
        $stmt = $pdo->prepare("
            SELECT c.id, c.candidate_name, c.party_name, c.symbol, c.logo,
                   COUNT(v.id) AS total_votes
            FROM candidates c
            LEFT JOIN votes v ON v.candidate_id = c.id
            WHERE c.election_id = ?
            GROUP BY c.id
            ORDER BY total_votes DESC
        ");
        $stmt->execute([$electionId]);
        $rows = $stmt->fetchAll();

        // Determine highest vote count and winners (handles ties)
        $maxVotes = 0;
        foreach ($rows as $r) {
            if ((int)$r['total_votes'] > $maxVotes) {
                $maxVotes = (int)$r['total_votes'];
            }
        }
        $winners = array_filter(
            $rows, 
            fn($r) => (int)$r['total_votes'] === $maxVotes && $maxVotes > 0
        );

        $totalVotesAll = array_sum(array_column($rows, 'total_votes'));
    ?>

        <div class="results-chart">
            <?php foreach ($rows as $r): 
                $isWinner   = ((int)$r['total_votes'] === $maxVotes && $maxVotes > 0);
                $percentage = $totalVotesAll > 0 
                    ? round(($r['total_votes'] / $totalVotesAll) * 100, 1) 
                    : 0;

                // Build logo path once
                $logoPath = 'assets/images/parties/' . ($r['logo'] ?? '');
                $absPath  = __DIR__ . '/' . $logoPath;
                $hasLogo  = !empty($r['logo']) && is_file($absPath);
            ?>
                <div class="result-row <?= $isWinner ? 'winner-row' : '' ?>">
                    <div class="result-header">
                        <?php if ($hasLogo): ?>
                            <img src="<?= e($logoPath) ?>" 
                                 alt="<?= e($r['party_name']) ?> logo" 
                                 class="result-logo">
                        <?php else: ?>
                            <span class="result-symbol"><?= e($r['symbol']) ?></span>
                        <?php endif; ?>

                        <span class="result-name"><?= e($r['candidate_name']) ?></span>
                        <span class="result-party"><?= e($r['party_name']) ?></span>

                        <?php if ($isWinner): ?>
                            <span class="badge badge-open">🏆 Winner</span>
                        <?php endif; ?>

                        <span class="result-count">
                            <?= (int)$r['total_votes'] ?> votes (<?= $percentage ?>%)
                        </span>
                    </div>

                    <div class="result-bar-track">
                        <div class="result-bar" style="width: <?= $percentage ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalVotesAll === 0): ?>
            <div class="alert alert-info">No votes were cast in this election.</div>
        <?php endif; ?>

        <?php if (count($winners) > 1): ?>
            <div class="alert alert-info">
                ⚖️ It's a tie between <?= count($winners) ?> candidates.
            </div>
        <?php endif; ?>

    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>