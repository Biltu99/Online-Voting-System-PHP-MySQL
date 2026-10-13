<?php
// ============================================================
// config/db.php
// Database connection + auto status sync
// Project: Online Voting System (College Assignment)
// Location: C:\xampp_new\htdocs\VotingSystemCollegeAssign
// ============================================================

// ------------------------------------------------------------
// Show errors during development (turn OFF before submission)
// ------------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------------------------------------------------
// Set PHP timezone to match MySQL (fixes vote "not started" bug)
// ------------------------------------------------------------
date_default_timezone_set('Asia/Kolkata');

// ------------------------------------------------------------
// Database credentials (default XAMPP)
// ------------------------------------------------------------
$host   = 'localhost';
$dbname = 'online_voting_college_assign';
$dbuser = 'root';
$dbpass = ''; // Default XAMPP password is empty

// ------------------------------------------------------------
// Create PDO connection
// ------------------------------------------------------------
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+05:30'"  // <-- Timezone fix
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ============================================================
// AUTO-STATUS SYNC (Bonus Feature)
// ------------------------------------------------------------
// Keeps the "status" column in the elections table updated
// automatically based on the current server time.
//
// Rule:
//   1. If end_time < NOW()           -> mark as 'ended'
//   2. If start_time <= NOW() < end  -> mark as 'open'
//
// This is wrapped in try/catch so a permission issue never
// breaks the site — dynamic helper get_election_status()
// will still work in PHP.
// ============================================================
try {
    // 1. Auto-close elections whose end_time has passed
    $pdo->exec("
        UPDATE elections
        SET status = 'ended'
        WHERE status != 'ended'
          AND end_time < NOW()
    ");

    // 2. Auto-open elections whose start_time has arrived
    $pdo->exec("
        UPDATE elections
        SET status = 'open'
        WHERE status = 'not_started'
          AND start_time <= NOW()
          AND end_time   >  NOW()
    ");
} catch (PDOException $e) {
    // Silent fail — status is still calculated dynamically
    // via get_election_status() in includes/functions.php
}