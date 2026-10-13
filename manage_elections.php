<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$errors = [];

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $start_time  = $_POST['start_time'] ?? '';
            $end_time    = $_POST['end_time'] ?? '';

            if ($title === '') $errors[] = 'Title is required.';
            if ($start_time === '' || $end_time === '') $errors[] = 'Start and end time required.';
            if (strtotime($end_time) <= strtotime($start_time)) $errors[] = 'End time must be after start time.';

            if (empty($errors)) {
                $stmt = $pdo->prepare("INSERT INTO elections (title, description, start_time, end_time, status) VALUES (?, ?, ?, ?, 'not_started')");
                $stmt->execute([$title, $description, $start_time, $end_time]);
                set_flash('success', 'Election created.');
                redirect('manage_elections.php');
            }
        }

        if ($action === 'update_status') {
            $id     = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if (in_array($status, ['not_started', 'open', 'ended'], true)) {
                $stmt = $pdo->prepare("UPDATE elections SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                set_flash('success', 'Election status updated.');
            }
            redirect('manage_elections.php');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM elections WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Election deleted.');
            redirect('manage_elections.php');
        }
    }
}

$elections = $pdo->query("SELECT * FROM elections ORDER BY start_time DESC")->fetchAll();

include 'includes/header.php';
?>

<section class="section">
    <h1>Manage Elections</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Create New Election</h2>
        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <label>Title
                <input type="text" name="title" required>
            </label>

            <label>Description
                <textarea name="description" rows="3"></textarea>
            </label>

            <label>Start Time
                <input type="datetime-local" name="start_time" required>
            </label>

            <label>End Time
                <input type="datetime-local" name="end_time" required>
            </label>

            <button type="submit" class="btn btn-primary">Create Election</button>
        </form>
    </div>

    <h2>Existing Elections</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Start</th>
                <th>End</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($elections as $el): 
            $status = get_election_status($el); ?>
            <tr>
                <td><?= e($el['title']) ?></td>
                <td><span class="badge badge-<?= $status ?>"><?= status_label($status) ?></span></td>
                <td><?= e(date('d M Y H:i', strtotime($el['start_time']))) ?></td>
                <td><?= e(date('d M Y H:i', strtotime($el['end_time']))) ?></td>
                <td class="actions">
                    <form method="post" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?= (int)$el['id'] ?>">
                        <select name="status">
                            <option value="not_started" <?= $el['status']=='not_started'?'selected':'' ?>>Not Started</option>
                            <option value="open" <?= $el['status']=='open'?'selected':'' ?>>Open</option>
                            <option value="ended" <?= $el['status']=='ended'?'selected':'' ?>>Ended</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                    </form>

                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this election?')">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$el['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include 'includes/footer.php'; ?>