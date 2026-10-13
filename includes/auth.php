<?php
// includes/auth.php
require_once __DIR__ . '/functions.php';

function require_voter() {
    if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'voter') {
        set_flash('error', 'Please log in as a voter to continue.');
        redirect('login.php');
    }
}

function require_admin() {
    if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        set_flash('error', 'Administrator access required.');
        redirect('admin_login.php');
    }
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}