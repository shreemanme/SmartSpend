<?php
/**
 * Page:      auth/logout.php
 * Component: Authentication — Logout
 * Developer: Shreeman Bhandari
 */

session_start();
session_unset();
session_destroy();
header('Location: /smartspend/auth/login.php');
exit;
