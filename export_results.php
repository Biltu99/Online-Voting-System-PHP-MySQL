<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$electionId = (int)($_GET['election_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$electionId]);
$election = $stmt->fetch();

if (!$election) {
    set_flash('error', 'Election not found.');
    redirect('admin_dashboard.php');
}

$stmt = $pdo->prepare("
    SELECT c.candidate_name, c.party_name, c.symbol,
           COUNT(v.id) AS total_votes
    FROM candidates c
    LEFT JOIN votes v ON v.candidate_id = c.id
    WHERE c.election_id = ?
    GROUP BY c.id
    ORDER BY total_votes DESC
");
$stmt->execute([$electionId]);
$rows = $stmt->fetchAll();

// Send CSV headers
$filename = 'results_' . preg_replace('/[^a-zA-Z0-9]/', '_', $election['title']) . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Election', $election['title']]);
fputcsv($out, ['Start', $election['start_time']]);
fputcsv($out, ['End', $election['end_time']]);
fputcsv($out, []);
fputcsv($out, ['Symbol', 'Candidate', 'Party', 'Votes']);

foreach ($rows as $r) {
    fputcsv($out, [$r['symbol'], $r['candidate_name'], $r['party_name'], $r['total_votes']]);
}

fclose($out);
exit;