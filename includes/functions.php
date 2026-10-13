<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function get_election_status($election) {
    $now   = new DateTime();
    $start = new DateTime($election['start_time']);
    $end   = new DateTime($election['end_time']);

    if ($election['status'] === 'ended' || $now > $end) {
        return 'ended';
    }
    if ($now < $start) {
        return 'not_started';
    }
    return 'open';
}

function status_label($status) {
    return match($status) {
        'open'        => 'Open',
        'ended'       => 'Ended',
        default       => 'Not Started'
    };
}