<?php
/**
 * Page:      index.php
 * Component: Entry Point — Redirect
 * Developer: Shreeman Bhandari
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /smartspend/dashboard/index.php');
} else {
    header('Location: /smartspend/auth/login.php');
}
exit;
