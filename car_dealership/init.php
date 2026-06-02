<?php
// Initializes the database schema and creates all required tables if they don't exist.
// Also auto-migrates existing tables to add any new columns added during development.
// This file is included by config.php on every page load.
require_once 'config.php';
try {
// --- Users table: stores login credentials and roles ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(255) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
role VARCHAR(50) NOT NULL DEFAULT 'user'
)
");
// --- Cars table: the main vehicle inventory ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS cars (
id INT AUTO_INCREMENT PRIMARY KEY,
make VARCHAR(100) NOT NULL,
model VARCHAR(100) NOT NULL,
year INT NOT NULL,
price DECIMAL(15, 2) NOT NULL,
mileage INT NOT NULL,
fuel_type VARCHAR(20) NOT NULL DEFAULT 'petrol',
description TEXT,
image_url VARCHAR(255),
status VARCHAR(50) DEFAULT 'available'
)
");
// Migrate existing cars table: add fuel_type if missing (for existing installs)
$car_cols = array_column($pdo->query("SHOW COLUMNS FROM cars")->fetchAll(), 'Field');
if (!in_array('fuel_type', $car_cols)) {
$pdo->exec("ALTER TABLE cars ADD COLUMN fuel_type VARCHAR(20) NOT NULL DEFAULT 'petrol' AFTER mileage");
}
// Migrate existing purchases table: add missing detail columns
$purchase_cols = array_column($pdo->query("SHOW COLUMNS FROM purchases")->fetchAll(), 'Field');
if (!in_array('full_name', $purchase_cols)) {
$pdo->exec("ALTER TABLE purchases ADD COLUMN full_name VARCHAR(150) NOT NULL DEFAULT '' AFTER car_id");
$pdo->exec("ALTER TABLE purchases ADD COLUMN phone VARCHAR(30) NOT NULL DEFAULT '' AFTER full_name");
$pdo->exec("ALTER TABLE purchases ADD COLUMN payment_method VARCHAR(100) NOT NULL DEFAULT '' AFTER phone");
$pdo->exec("ALTER TABLE purchases ADD COLUMN delivery_option VARCHAR(100) NOT NULL DEFAULT '' AFTER payment_method");
}
// --- Purchases table: records buy requests made by users ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS purchases (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
car_id INT NOT NULL,
full_name VARCHAR(150) NOT NULL DEFAULT '',
phone VARCHAR(30) NOT NULL DEFAULT '',
payment_method VARCHAR(100) NOT NULL DEFAULT '',
delivery_option VARCHAR(100) NOT NULL DEFAULT '',
purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
status VARCHAR(50) DEFAULT 'pending',
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
)
");
// --- Test drives table: stores test drive booking requests ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS test_drives (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
car_id INT NOT NULL,
full_name VARCHAR(150) NOT NULL DEFAULT '',
phone VARCHAR(30) NOT NULL DEFAULT '',
preferred_date VARCHAR(50) NOT NULL,
preferred_time VARCHAR(50) NOT NULL,
notes TEXT,
status VARCHAR(50) DEFAULT 'pending',
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
)
");
// --- Financing table: stores financing pre-approval applications ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS financing (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
car_id INT NOT NULL,
down_payment DECIMAL(10, 2) NOT NULL,
term_months INT NOT NULL,
status VARCHAR(50) DEFAULT 'pending',
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
)
");
// --- Wishlists table: lets users save favourite vehicles ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS wishlists (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
car_id INT NOT NULL,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY unique_wishlist (user_id, car_id),
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
)
");
// --- Seed the default admin account if it doesn't already exist ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute(['susantabhatta51@gmail.com']);
if ($stmt->fetchColumn() == 0) {
$password = password_hash('useradmin', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
$stmt->execute(['susantabhatta51@gmail.com', $password, 'admin']);
}
} catch (PDOException $e) {
echo "Initialization error: " . $e->getMessage();
}
?>
