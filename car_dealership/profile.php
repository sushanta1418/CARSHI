<?php
// Profile page: shows the logged-in user's account info and their purchase history.
require_once 'init.php';
require_once 'auth.php';
requireLogin(); // Redirect to login if not authenticated
// Fetch the current user's account record
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
// Fetch all purchase requests the user has made, newest first
$stmt = $pdo->prepare("
SELECT p.*, c.make, c.model, c.year, c.price, c.image_url
FROM purchases p
JOIN cars c ON p.car_id = c.id
WHERE p.user_id = ?
ORDER BY p.purchase_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll();
require_once 'header.php';
?>
<section style="padding: 100px 20px 60px;">
<div class="container" style="max-width: 900px;">
<h1 class="animate-on-scroll"
style="margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
<i class="fa-solid fa-circle-user text-primary"></i> My Profile
</h1>
<div class="glass-panel animate-on-scroll" style="padding: 30px; margin-bottom: 40px;">
<div style="display: flex; gap: 20px; align-items: center;">
<div
style="width: 80px; height: 80px; background: var(--dark-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary); border: 2px solid var(--border-color);">
<?= strtoupper(substr($user['email'], 0, 1)) ?>
</div>
<div>
<h3 style="margin-bottom: 5px;">
<?= htmlspecialchars($user['email']) ?>
</h3>
<p style="color: var(--muted-text);">Member since
<?= date('F Y') ?>
</p>
</div>
</div>
</div>
<h2 class="animate-on-scroll" style="margin-bottom: 25px;"><i class="fa-solid fa-clock-rotate-left"></i>
Purchase Requests</h2>
<div class="glass-panel animate-on-scroll" style="padding: 30px;">
<?php if (empty($purchases)): ?>
<div class="text-center" style="padding: 40px 0;">
<i class="fa-solid fa-cart-arrow-down"
style="font-size: 3rem; color: var(--muted-text); margin-bottom: 20px;"></i>
<p style="color: var(--muted-text); font-size: 1.1rem; margin-bottom: 20px;">You haven't requested to
purchase any vehicles yet.</p>
<a href="cars.php" class="btn btn-primary">Browse Inventory</a>
</div>
<?php else: ?>
<div style="display: flex; flex-direction: column; gap: 20px;">
<?php foreach ($purchases as $p): ?>
<div
style="display: flex; gap: 20px; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); align-items: center; flex-wrap: wrap;">
<img src="<?= htmlspecialchars($p['image_url'] ?? 'https://via.placeholder.com/150x100/1e293b/fff?text=Car') ?>"
alt="car" style="width: 150px; height: 100px; object-fit: cover; border-radius: 6px;">
<div style="flex: 1; min-width: 250px;">
<h3 style="margin-bottom: 8px;">
<a href="car_details.php?id=<?= $p['car_id'] ?>"
style="color: var(--light-text); text-decoration: none;">
<?= htmlspecialchars($p['year'] . ' ' . $p['make'] . ' ' . $p['model']) ?>
</a>
</h3>
<div style="color: var(--primary); font-weight: bold; font-size: 1.2rem; margin-bottom: 10px;">
Rs
<?= number_format($p['price']) ?>
</div>
<div style="color: var(--muted-text); font-size: 0.9rem;">
Requested on:
<?= date('M j, Y', strtotime($p['purchase_date'])) ?>
</div>
</div>
<div style="text-align: right; min-width: 120px;">
<div style="margin-bottom: 5px; font-size: 0.85rem; color: var(--muted-text);">Status</div>
<span class="car-badge <?= $p['status'] === 'pending' ? '' : 'sold' ?>"
style="position: static; font-size: 1rem; padding: 8px 15px;">
<?= strtoupper($p['status']) ?>
</span>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</section>
<?php require_once 'footer.php'; ?>
