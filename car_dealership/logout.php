<?php
// Destroy the session and redirect to the homepage
require_once 'auth.php';
logoutUser();
header("Location: index.php");
exit;
?>
