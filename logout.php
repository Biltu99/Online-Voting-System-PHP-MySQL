<?php
require_once 'includes/functions.php';
$_SESSION = [];
session_destroy();
session_start();
set_flash('success', 'You have been logged out.');
header('Location: index.php');
exit;