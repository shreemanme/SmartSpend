<?php
/**
 * Component: Security Middleware
 * Purpose:   Verify administrative access for admin panel routes
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Access denied. Administrator privileges required.'
    ];
    header('Location: /smartspend/auth/login.php');
    exit;
}
