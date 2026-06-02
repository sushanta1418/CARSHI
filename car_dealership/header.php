<?php
// Shared site header: outputs <head>, loads assets, and renders the navigation bar.
// Included at the top of every page that uses the standard layout.
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LuxeDrive Auto</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap"
rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-mode">
<!-- Navigation bar -->
<nav class="navbar">
<div class="nav-container">
<a href="index.php" class="logo">
<i class="fa-solid fa-car-side"></i> LuxeDrive
</a>
<!-- Hamburger button for mobile -->
<button class="mobile-menu-btn" id="mobileMenuBtn">
<i class="fa-solid fa-bars"></i>
</button>
<ul class="nav-links" id="navLinks">
<li><a href="index.php">Home</a></li>
<li><a href="cars.php">Inventory</a></li>
<li><a href="financing.php">Financing</a></li>
<li><a href="about.php">About Us</a></li>
<li><a href="contact.php">Contact</a></li>
<?php if (isAdmin()): ?>
<li class="nav-divider"></li>
<li><a href="admin.php" class="admin-link"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
<?php endif; ?>
<?php if (isLoggedIn()): ?>
<?php if (!isAdmin()): ?>
<li><a href="profile.php" class="btn btn-outline" style="border:none; padding: 8px;"><i
class="fa-solid fa-user"></i> Profile</a></li>
<li><a href="wishlist.php" class="btn btn-outline" style="border:none; padding: 8px;"><i
class="fa-solid fa-heart" style="color:#f43f5e;"></i> Wishlist</a></li>
<?php endif; ?>
<li><a href="logout.php" class="btn btn-outline">Logout</a></li>
<?php else: ?>
<li><a href="login.php" class="btn btn-primary">Login</a></li>
<?php endif; ?>
</ul>
</div>
</nav>
<main class="main-content">
