<?php
require_once 'init.php';
require_once 'header.php';
// Fetch a few featured/recent cars
$stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' ORDER BY id DESC LIMIT 3");
$featured_cars = $stmt->fetchAll();
?>
<!-- Hero Section with Video Background Simulation -->
<section class="hero">
<img src="https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80"
alt="Luxury Car" class="hero-video-bg">
<div class="hero-overlay"></div>
<div class="hero-content animate-on-scroll">
<h1>Discover Pure Driving Pleasure</h1>
<p>Premium selection of luxury and performance vehicles tailored for you.</p>
<div class="hero-buttons">
<a href="cars.php" class="btn btn-primary">Browse Inventory</a>
<?php if (!isLoggedIn()): ?>
<a href="register.php" class="btn btn-outline">Join the Club</a>
<?php endif; ?>
</div>
</div>
</section>
<!-- Featured Categories Section -->
<section style="padding: 80px 20px; background-color: var(--dark-bg);">
<div class="container">
<h2 class="section-title animate-on-scroll">Why Choose LuxeDrive</h2>
<div
style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-top: 50px;">
<div class="glass-panel text-center animate-on-scroll" style="padding: 40px 20px;">
<i class="fa-solid fa-gem text-primary" style="font-size: 3rem; margin-bottom: 20px;"></i>
<h3 style="margin-bottom: 15px; font-size: 1.5rem;">Premium Quality</h3>
<p style="color: var(--muted-text);">Every vehicle in our inventory undergoes a rigorous 150-point
inspection.</p>
</div>
<div class="glass-panel text-center animate-on-scroll" style="padding: 40px 20px; transition-delay: 0.1s;">
<i class="fa-solid fa-handshake-angle text-primary" style="font-size: 3rem; margin-bottom: 20px;"></i>
<h3 style="margin-bottom: 15px; font-size: 1.5rem;">Expert Financing</h3>
<p style="color: var(--muted-text);">We offer flexible financing options tailored to your specific
needs.</p>
</div>
<div class="glass-panel text-center animate-on-scroll" style="padding: 40px 20px; transition-delay: 0.2s;">
<i class="fa-solid fa-headset text-primary" style="font-size: 3rem; margin-bottom: 20px;"></i>
<h3 style="margin-bottom: 15px; font-size: 1.5rem;">24/7 Support</h3>
<p style="color: var(--muted-text);">Our dedicated support team is always here to assist you.</p>
</div>
</div>
</div>
</section>
<!-- Featured Cars Section -->
<section style="padding: 80px 20px; background: linear-gradient(180deg, var(--dark-bg) 0%, rgba(30,41,59,0.5) 100%);">
<div class="container">
<h2 class="section-title animate-on-scroll">Newly Arrived</h2>
<div class="cars-grid">
<?php if (empty($featured_cars)): ?>
<div class="text-center" style="grid-column: 1 / -1; padding: 40px; color: var(--muted-text);">
<p>No cars available at the moment. Check back soon!</p>
</div>
<?php else: ?>
<?php foreach ($featured_cars as $index => $car): ?>
<div class="car-card animate-on-scroll" style="transition-delay: <?= $index * 0.1 ?>s;">
<div class="car-image-container">
<img src="<?= htmlspecialchars($car['image_url'] ?? 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') ?>"
alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" class="car-image">
<!-- Show New Arrival badge for the first item -->
<?php if ($index === 0): ?>
<span class="car-badge">New Arrival</span>
<?php endif; ?>
</div>
<div class="car-info">
<h3 class="car-title">
<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>
</h3>
<div class="car-price">Rs
<?= number_format($car['price'], 2) ?>
</div>
<div class="car-details">
<div class="car-detail-item">
<i class="fa-regular fa-calendar text-primary"></i>
<?= htmlspecialchars($car['year']) ?>
</div>
<div class="car-detail-item">
<i class="fa-solid <?= $is_ev ? 'fa-bolt' : 'fa-gauge-high' ?> text-primary"></i>
<?= number_format($car['mileage']) ?>         <?= $is_ev ? 'km range' : 'km/L' ?>
</div>
</div>
<div class="car-actions">
<a href="car_details.php?id=<?= $car['id'] ?>" class="btn btn-outline" style="flex:1;"><i
class="fa-solid fa-eye"></i> View</a>
<a href="car_details.php?id=<?= $car['id'] ?>#buy" class="btn btn-primary" style="flex:1;"><i
class="fa-solid fa-cart-shopping"></i> Buy</a>
</div>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="text-center animate-on-scroll" style="margin-top: 40px;">
<a href="cars.php" class="btn btn-outline" style="padding: 12px 30px; font-size: 1.1rem;">View All Inventory
<i class="fa-solid fa-arrow-right" style="margin-left: 10px;"></i></a>
</div>
</div>
</section>
<!-- Call to action -->
<section
style="padding: 100px 20px; background: url('https://images.unsplash.com/photo-1549399542-7e3f8b79c341?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover fixed; position: relative;">
<div style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(15, 23, 42, 0.85);"></div>
<div class="container text-center animate-on-scroll" style="position: relative; z-index: 1;">
<h2 style="font-size: 3rem; margin-bottom: 20px;">Ready to elevate your drive?</h2>
<p style="font-size: 1.2rem; color: var(--muted-text); max-width: 600px; margin: 0 auto 40px;">
Join our exclusive community and get access to member-only events, private viewings, and priority test
drives.
</p>
<?php if (!isLoggedIn()): ?>
<a href="register.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 40px;">Create an
Account</a>
<?php else: ?>
<a href="cars.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 40px;">Find Your Next Car</a>
<?php endif; ?>
</div>
</section>
<?php require_once 'footer.php'; ?>
