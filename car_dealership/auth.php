<?php
// Starts the session and provides authentication helper functions used site-wide
session_start();
// Returns true if the user is logged in
function isLoggedIn()
{
return isset($_SESSION['user_id']);
}
// Returns true if the logged-in user has the 'admin' role
function isAdmin()
{
return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
// Redirects to the login page if the user is not logged in
function requireLogin()
{
if (!isLoggedIn()) {
header("Location: login.php");
exit;
}
}
// Redirects non-admins away; must be logged in AND have the admin role
function requireAdmin()
{
requireLogin();
if (!isAdmin()) {
header("Location: index.php");
exit;
}
}
// Stores the user's ID and role in the session to mark them as logged in
function loginUser($user_id, $role)
{
$_SESSION['user_id'] = $user_id;
$_SESSION['user_role'] = $role;
}
// Destroys the session, effectively logging the user out
function logoutUser()
{
session_unset();
session_destroy();
}
?>
