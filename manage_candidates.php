<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$errors = [];
$elections = $pdo->query("SELECT * FROM elections ORDER BY start_time DESC")->fetchAll();

// Ensure upload folder exists
$uploadDir = __DIR__ . '/assets/images/parties/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token.';
    } else {
        $action = $_POST['action'] ?? '';

        // ============================================================
        // CREATE
        // ============================================================
        if ($action === 'create') {
            $election_id    = (int)($_POST['election_id'] ?? 0);
            $candidate_name = trim($_POST['candidate_name'] ?? '');
            $party_name     = trim($_POST['party_name'] ?? '');
            $symbol         = trim($_POST['symbol'] ?? '');

            if (!$election_id)        $errors[] = 'Please select an election.';
            if ($candidate_name === '') $errors[] = 'Candidate name required.';
            if ($party_name === '')     $errors[] = 'Party name required.';
            if ($symbol === '')         $errors[] = 'Symbol required.';

            // ---- Handle logo upload ----
            $logoFilename = null;
            if (!empty($_FILES['logo']['name'])) {
                $file = $_FILES['logo'];

                // Check for upload errors
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Logo upload failed (error code ' . $file['error'] . ').';
                } else {
                    // Validate size (max 2 MB)
                    if ($file['size'] > 2 * 1024 * 1024) {
                        $errors[] = 'Logo must be smaller than 2 MB.';
                    }

                    // Validate MIME type
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mime, $allowed, true)) {
                        $errors[] = 'Logo must be JPG, PNG, GIF, or WEBP.';
                    }

                    // Generate a safe unique filename
                    if (empty($errors)) {
                        $ext = match($mime) {
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/gif'  => 'gif',
                            'image/webp' => 'webp',
                            default      => 'png'
                        };
                        $logoFilename = 'party_' . $election_id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $targetPath   = $uploadDir . $logoFilename;

                        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                            $errors[] = 'Could not save the uploaded logo.';
                            $logoFilename = null;
                        }
                    }
                }
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("
                    INSERT INTO candidates 
                        (election_id, candidate_name, party_name, symbol, logo) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$election_id, $candidate_name, $party_name, $symbol, $logoFilename]);
                set_flash('success', 'Candidate added successfully.');
                redirect('manage_candidates.php');
            }
        }

        // ============================================================
        // DELETE
        // ============================================================
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);

            // Fetch logo filename to delete the file too
            $stmt = $pdo->prepare("SELECT logo FROM candidates WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if ($row && !empty($row['logo'])) {
                $filePath = $uploadDir . $row['logo'];
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }

            $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Candidate deleted.');
            redirect('manage_candidates.php');
        }
    }
}

// Fetch candidates with election title
$candidates = $pdo->query("
    SELECT c.*, e.title AS election_title
    FROM candidates c
    JOIN elections e ON e.id = c.election_id
    ORDER BY e.start_time DESC, c.id ASC
")->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <h1>Manage Candidates</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Add Candidate</h2>
        <form method="post" enctype="multipart/form-data" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <label>Election
                <select name="election_id" required>
                    <option value="">-- Select Election --</option>
                    <?php foreach ($elections as $el): ?>
                        <option value="<?= (int)$el['id'] ?>"><?= e($el['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Candidate Name
                <input type="text" name="candidate_name" required>
            </label>

            <label>Party Name
                <input type="text" name="party_name" required>
            </label>

            <label>Symbol (emoji or short text — used as fallback if no logo)
                <input type="text" name="symbol" maxlength="10" required placeholder="e.g. 🌟">
            </label>

            <label>Party Logo (optional — JPG / PNG / GIF / WEBP, max 2 MB)
                <input type="file" name="logo" accept="image/*">
            </label>

            <button type="submit" class="btn btn-primary">Add Candidate</button>
        </form>
    </div>

    <h2>Existing Candidates</h2>
    <?php if (empty($candidates)): ?>
        <p>No candidates yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Logo / Symbol</th>
                    <th>Name</th>
                    <th>Party</th>
                    <th>Election</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($candidates as $c): ?>
                <tr>
                    <td style="font-size:1.5rem">
                        <?php if (!empty($c['logo']) && is_file($uploadDir . $c['logo'])): ?>
                            <img src="assets/images/parties/<?= e($c['logo']) ?>" 
                                 alt="<?= e($c['party_name']) ?>" 
                                 style="width:40px;height:40px;object-fit:contain;vertical-align:middle;border-radius:6px;">
                        <?php else: ?>
                            <?= e($c['symbol']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= e($c['candidate_name']) ?></td>
                    <td><?= e($c['party_name']) ?></td>
                    <td><?= e($c['election_title']) ?></td>
                    <td>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this candidate?')">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>