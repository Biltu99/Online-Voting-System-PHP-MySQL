<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'config/db.php';

if (is_logged_in()) {
    redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php');
}

$errors = [];
$old = [
    'full_name' => '', 'username' => '', 'email' => '',
    'voter_epic_no' => '', 'date_of_birth' => '', 'gender' => '',
    'mobile' => '', 'address_line' => '', 'state' => '',
    'district' => '', 'pincode' => '', 'ward_number' => '',
    'assembly_constituency' => '', 'parliamentary_constituency' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token. Please try again.';
    }

    // Collect all inputs
    foreach ($old as $key => $_) {
        $old[$key] = trim($_POST[$key] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Identity & Account Validation ----
    if ($old['full_name'] === '') $errors[] = 'Full name is required.';

    if ($old['username'] === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $old['username'])) {
        $errors[] = 'Username must be 3–30 chars (letters, numbers, underscore).';
    }

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)  $errors[] = 'Passwords do not match.';

    // ---- Voter Identity ----
    if ($old['voter_epic_no'] === '') {
        $errors[] = 'Voter EPIC number is required.';
    } elseif (!preg_match('/^[A-Za-z0-9]{6,20}$/', $old['voter_epic_no'])) {
        $errors[] = 'EPIC number must be 6–20 letters/digits.';
    }

    // ---- Date of Birth (must be 18+) ----
    if ($old['date_of_birth'] === '') {
        $errors[] = 'Date of birth is required.';
    } else {
        $dob = DateTime::createFromFormat('Y-m-d', $old['date_of_birth']);
        if (!$dob) {
            $errors[] = 'Invalid date of birth format.';
        } else {
            $today = new DateTime();
            $age   = $today->diff($dob)->y;
            if ($age < 18) {
                $errors[] = 'You must be at least 18 years old to register.';
            }
        }
    }

    // ---- Gender ----
    if (!in_array($old['gender'], ['Male','Female','Other'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    // ---- Mobile ----
    if (!preg_match('/^[0-9]{10}$/', $old['mobile'])) {
        $errors[] = 'Mobile number must be exactly 10 digits.';
    }

    // ---- Address ----
    if ($old['address_line'] === '')      $errors[] = 'Address Line is required.';
    if ($old['state'] === '')             $errors[] = 'State is required.';
    if ($old['district'] === '')          $errors[] = 'District is required.';
    if (!preg_match('/^[0-9]{6}$/', $old['pincode'])) {
        $errors[] = 'PIN Code must be exactly 6 digits.';
    }
    if ($old['ward_number'] === '')       $errors[] = 'Ward Number is required.';
    if ($old['assembly_constituency'] === '')      $errors[] = 'Assembly Constituency is required.';
    if ($old['parliamentary_constituency'] === '') $errors[] = 'Parliamentary Constituency is required.';

    // ---- Uniqueness checks ----
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id FROM users 
            WHERE username = ? 
               OR email = ? 
               OR voter_epic_no = ?
               OR mobile = ?
        ");
        $stmt->execute([
            $old['username'], $old['email'], $old['voter_epic_no'], $old['mobile']
        ]);
        if ($stmt->fetch()) {
            $errors[] = 'Username, email, EPIC number, or mobile is already registered.';
        }
    }

    // ---- Insert ----
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (
                full_name, username, email, voter_epic_no, date_of_birth, gender,
                mobile, address_line, state, district, pincode, ward_number,
                assembly_constituency, parliamentary_constituency,
                password_hash, role
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?,
                ?, 'voter'
            )
        ");
        $stmt->execute([
            $old['full_name'], $old['username'], $old['email'],
            strtoupper($old['voter_epic_no']), $old['date_of_birth'], $old['gender'],
            $old['mobile'], $old['address_line'], $old['state'],
            $old['district'], $old['pincode'], $old['ward_number'],
            $old['assembly_constituency'], $old['parliamentary_constituency'],
            $hash
        ]);

        set_flash('success', 'Registration successful! Please log in.');
        redirect('login.php');
    }
}

include 'includes/header.php';
?>

<section class="form-section form-section-wide">
    <h1>Voter Registration</h1>
    <p class="muted" style="text-align:center; margin-bottom:20px;">
        All fields marked with <span style="color:#dc2626">*</span> are required.
    </p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <!-- ================= ACCOUNT ================= -->
        <h2 class="form-section-title">🔑 Account Information</h2>
        <div class="form-grid">
            <label class="span-2">Full Name <span class="req">*</span>
                <input type="text" name="full_name" required value="<?= e($old['full_name']) ?>">
            </label>

            <label>Username <span class="req">*</span>
                <input type="text" name="username" required value="<?= e($old['username']) ?>">
            </label>

            <label>Email <span class="req">*</span>
                <input type="email" name="email" required value="<?= e($old['email']) ?>">
            </label>

            <label>Password <span class="req">*</span>
                <input type="password" name="password" id="passwordInput" required minlength="6">
                <div class="strength-meter">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <small id="strengthText" class="muted">Enter a password</small>
            </label>

            <label>Confirm Password <span class="req">*</span>
                <input type="password" name="confirm_password" required minlength="6">
            </label>
        </div>

        <!-- ================= IDENTITY ================= -->
        <h2 class="form-section-title">🪪 Voter Identity</h2>
        <div class="form-grid">
            <label>Voter EPIC Number <span class="req">*</span>
                <input type="text" name="voter_epic_no" required maxlength="20"
                       value="<?= e($old['voter_epic_no']) ?>"
                       placeholder="e.g. ABC1234567">
            </label>

            <label>Date of Birth <span class="req">*</span>
                <input type="date" name="date_of_birth" required
                       max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                       value="<?= e($old['date_of_birth']) ?>">
                <small class="muted">You must be at least 18 years old.</small>
            </label>

            <label>Gender <span class="req">*</span>
                <select name="gender" required>
                    <option value="">-- select --</option>
                    <option value="Male"   <?= $old['gender']==='Male'   ? 'selected':'' ?>>Male</option>
                    <option value="Female" <?= $old['gender']==='Female' ? 'selected':'' ?>>Female</option>
                    <option value="Other"  <?= $old['gender']==='Other'  ? 'selected':'' ?>>Other</option>
                </select>
            </label>

            <label>Mobile Number <span class="req">*</span>
                <input type="tel" name="mobile" required
                       pattern="[0-9]{10}" maxlength="10"
                       title="Enter exactly 10 digits"
                       value="<?= e($old['mobile']) ?>"
                       placeholder="10-digit mobile number">
            </label>
        </div>

        <!-- ================= ADDRESS ================= -->
        <h2 class="form-section-title">🏠 Address Details</h2>
        <div class="form-grid">
            <label class="span-2">Address Line <span class="req">*</span>
                <input type="text" name="address_line" required maxlength="255"
                       value="<?= e($old['address_line']) ?>"
                       placeholder="House no., street, locality">
            </label>

            <label>State <span class="req">*</span>
                <input type="text" name="state" required value="<?= e($old['state']) ?>">
            </label>

            <label>District <span class="req">*</span>
                <input type="text" name="district" required value="<?= e($old['district']) ?>">
            </label>

            <label>PIN Code <span class="req">*</span>
                <input type="text" name="pincode" required
                       pattern="[0-9]{6}" maxlength="6"
                       title="Enter exactly 6 digits"
                       value="<?= e($old['pincode']) ?>"
                       placeholder="6-digit PIN">
            </label>

            <label>Ward Number <span class="req">*</span>
                <input type="text" name="ward_number" required maxlength="20"
                       value="<?= e($old['ward_number']) ?>">
            </label>

            <label>Assembly Constituency <span class="req">*</span>
                <input type="text" name="assembly_constituency" required maxlength="100"
                       value="<?= e($old['assembly_constituency']) ?>">
            </label>

            <label>Parliamentary Constituency <span class="req">*</span>
                <input type="text" name="parliamentary_constituency" required maxlength="100"
                       value="<?= e($old['parliamentary_constituency']) ?>">
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Register</button>
        <p class="muted" style="text-align:center; margin-top:10px;">
            Already registered? <a href="login.php">Login here</a>
        </p>
    </form>
</section>

<?php include 'includes/footer.php'; ?>