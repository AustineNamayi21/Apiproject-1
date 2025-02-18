<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = [];

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Destroy the session
session_destroy();

// Set a success message in the session
$_SESSION['success'] = "You have been logged out successfully.";

// Redirect to the admin login page
header("Location: admin_login.php");
exit();
?>