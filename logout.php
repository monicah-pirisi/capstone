<?php
/**
 * Samburu EWS: Logout
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/Auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
Auth::logout();
header('Location: ' . base_url('index.php'));
exit;
