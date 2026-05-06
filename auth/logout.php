<?php
/**
 * Page:      auth/logout.php
 * Component: Authentication — Logout
 * Developer: Nandan Kumar Yadav (User & Account Management)
 */

session_start();
session_unset();
session_destroy();
header('Location: /smartspend/auth/login.php');
exit;
