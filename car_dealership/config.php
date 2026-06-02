<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_dealership');
define('DB_USER', 'root');
define('DB_PASS', '');
try {
// Step 1: Connect without a database selected, then create the DB if it doesn't exist
$pdo_init = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
$pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo_init->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
// Step 2: Connect to the actual car_dealership database
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);         // Throw exceptions on error
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);    // Always return associative arrays
} catch (PDOException $e) {
die("Database connection failed: " . $e->getMessage());
}
?>
