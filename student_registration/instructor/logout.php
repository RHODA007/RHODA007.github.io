<?php
session_start();

// Unset all instructor session variables
unset($_SESSION['instructor']);

// Or clear everything if you want
// session_unset();
// session_destroy();

// Redirect to login page
header("Location: login.php?logout=success");
exit;
