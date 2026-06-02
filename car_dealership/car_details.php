<?php
// Car detail page: shows full spec, buy form, and wishlist toggle for a single vehicle.
require_once 'init.php';
require_once 'header.php';
// Validate the car ID from the URL; redirect to inventory if missing or invalid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
header("Location: cars.php");
exit;
}
$car_id = (int) $_GET['id'];
// Fetch the car record
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();
// Redirect if the car doesn't exist
if (!$car) {
header("Location: cars.php");
exit;
}
// Check if this car is already in the logged-in user's wishlist
$is_wishlisted = false;
if (isLoggedIn() && !isAdmin()) {
$wl = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND car_id = ?");
$wl->execute([$_SESSION['user_id'], $car_id]);
$is_wishlisted = (bool) $wl->fetch();
}
// Handle the "Buy Now" / "Acquisition" form submission
$buy_message = '';
$buy_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
if (!isLoggedIn()) {
header("Location: login.php");
exit;
}
// Prevent purchase of an already-sold vehicle
if ($car['status'] === 'sold') {
$buy_error = "Sorry, this vehicle has already been sold.";
} else {
$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
$delivery_option = trim($_POST['delivery_option'] ?? '');
// Insert purchase request and mark car as sold
$stmt = $pdo->prepare("INSERT INTO purchases (user_id, car_id, full_name, phone, payment_method, delivery_option) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$user_id, $car_id, $full_name, $phone, $payment_method, $delivery_option])) {
$pdo->prepare("UPDATE cars SET status = 'sold' WHERE id = ?")->execute([$car_id]);
$buy_message = "Congratulations! Your purchase request has been submitted. Our team will contact you shortly.";
$car['status'] = 'sold'; // Reflect status change immediately on the page
} else {
$buy_error = "An error occurred while processing your request. Please try again.";
}
}
}
?>
<section style="padding: 100px 20px 60px;">
<div class="container">
<?php if ($buy_message): ?>
<div class="alert alert-success animate-on-scroll">
<i class="fa-solid fa-check-circle"></i>
<?= htmlspecialchars($buy_message) ?>
</div>
<?php endif; ?>
<?php if ($buy_error): ?>
<div class="alert alert-danger animate-on-scroll">
<i class="fa-solid fa-triangle-exclamation"></i>
<?= htmlspecialchars($buy_error) ?>
</div>
<?php endif; ?>
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px;">
<a href="cars.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Inventory</a>
<div style="display:flex;gap:10px;align-items:center;">
<?php if ($car['status'] === 'sold'): ?>
<span class="car-badge sold" style="position: static; font-size: 1rem; padding: 8px 16px;">SOLD
OUT</span>
<?php endif; ?>
<?php if (isLoggedIn() && !isAdmin()): ?>
<button id="detail-wishlist-btn" class="btn btn-outline" data-car-id="<?= $car_id ?>"
style="display:flex;align-items:center;gap:8px;<?= $is_wishlisted ? 'color:#f43f5e;border-color:#f43f5e;' : '' ?>">
<i class="<?= $is_wishlisted ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
<span><?= $is_wishlisted ? 'Saved' : 'Save' ?></span>
</button>
<?php endif; ?>
<?php if ($car['status'] !== 'sold'): ?>
<a href="test_drive.php?car_id=<?= $car_id ?>" class="btn btn-outline"
style="display:flex;align-items:center;gap:8px;">
<i class="fa-solid fa-calendar-check"></i> Book Test Drive
</a>
<?php endif; ?>
</div>
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
<!-- Left Column: Image -->
<div class="animate-on-scroll">
<div
style="border-radius: 12px; overflow: hidden; box-shadow: var(--glass-shadow); border: 1px solid var(--border-color);">
<img src="<?= htmlspecialchars($car['image_url'] ?? 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80') ?>"
alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>"
style="width: 100%; height: auto; display: block; object-fit: cover;">
</div>
</div>
<!-- Right Column: Details & Action -->
<div class="animate-on-scroll glass-panel" style="padding: 40px;">
<h1 style="font-size: 2.5rem; margin-bottom: 15px; color: var(--light-text);">
<?= htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']) ?>
</h1>
<div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 30px;">
Rs
<?= number_format($car['price'], 2) ?>
</div>
<div
style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 30px;">
<div>
<span
style="color: var(--muted-text); font-size: 0.9rem; display: block; margin-bottom: 5px;">Make</span>
<strong style="font-size: 1.1rem;">
<?= htmlspecialchars($car['make']) ?>
</strong>
</div>
<div>
<span
style="color: var(--muted-text); font-size: 0.9rem; display: block; margin-bottom: 5px;">Model</span>
<strong style="font-size: 1.1rem;">
<?= htmlspecialchars($car['model']) ?>
</strong>
</div>
<div>
<span
style="color: var(--muted-text); font-size: 0.9rem; display: block; margin-bottom: 5px;">Year</span>
<strong style="font-size: 1.1rem;">
<?= htmlspecialchars($car['year']) ?>
</strong>
</div>
<div>
<?php $is_ev = ($car['fuel_type'] ?? '') === 'electric'; ?>
<span style="color: var(--muted-text); font-size: 0.9rem; display: block; margin-bottom: 5px;">
<?= $is_ev ? 'Electric Range' : 'Fuel Efficiency' ?>
</span>
<strong style="font-size: 1.1rem;">
<i class="fa-solid <?= $is_ev ? 'fa-bolt' : 'fa-gauge-high' ?>"
style="margin-right:4px;"></i><?= number_format($car['mileage']) ?>
<?= $is_ev ? 'km range' : 'km/L' ?>
</strong>
</div>
</div>
<div style="margin-bottom: 40px;">
<h3 style="margin-bottom: 15px; font-size: 1.2rem;">Vehicle Description</h3>
<p style="color: var(--muted-text); line-height: 1.6;">
<?= nl2br(htmlspecialchars($car['description'] ?? 'No detailed description available for this vehicle. Please contact our sales team for more information.')) ?>
</p>
</div>
<div id="buy" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border-color);">
<h3 style="margin-bottom: 25px; font-size: 1.5rem;">Acquisition Procedure</h3>
<?php if ($car['status'] !== 'sold'): ?>
<?php if (isLoggedIn()): ?>
<form method="POST" action="car_details.php?id=<?= $car_id ?>" class="auth-form"
style="max-width: 100%;">
<input type="hidden" name="action" value="buy">
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
<div class="form-group">
<label for="full_name">Full Legal Name *</label>
<input type="text" id="full_name" name="full_name" class="form-control" required
placeholder="John Doe">
</div>
<div class="form-group">
<label for="phone">Phone Number *</label>
<input type="tel" id="phone" name="phone" class="form-control" required
placeholder="+977 0000000000">
</div>
<div class="form-group">
<label for="payment_method">Preferred Payment Method *</label>
<select id="payment_method" name="payment_method" class="form-control" required
style="background: var(--dark-bg); padding-left: 15px;">
<option value="">-- Select Method --</option>
<option value="Wire Transfer">Direct Wire Transfer</option>
<option value="Dealership Finance">Dealership Financing</option>
<option value="External Bank">External Bank Finance</option>
</select>
</div>
<div class="form-group">
<label for="delivery_option">Delivery Preference *</label>
<select id="delivery_option" name="delivery_option" class="form-control" required
style="background: var(--dark-bg); padding-left: 15px;">
<option value="">-- Select Preference --</option>
<option value="Dealership Pickup">VIP Dealership Pickup</option>
<option value="Home Delivery">Enclosed Home Delivery</option>
</select>
</div>
</div>
<div class="form-group"
style="margin-top: 15px; display: flex; align-items: flex-start; gap: 10px;">
<input type="checkbox" id="terms" name="terms" required
style="margin-top: 6px; transform: scale(1.2);">
<label for="terms"
style="font-weight: normal; color: var(--muted-text); font-size: 0.95rem;">
I confirm my intent to acquire this vehicle and agree to the <a href="#"
class="text-primary">Terms & Conditions</a> of purchase.
A representative will contact me to finalize the transaction.
</label>
</div>
<button type="submit" class="btn btn-primary btn-block"
style="margin-top: 20px; font-size: 1.2rem; padding: 15px; display: flex; align-items: center; justify-content: center; gap: 10px;">
<i class="fa-solid fa-file-signature"></i> Submit Formal Acquisition Request
</button>
</form>
<?php else: ?>
<div
style="background: rgba(255,255,255,0.05); padding: 30px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
<i class="fa-solid fa-user-lock"
style="font-size: 3rem; color: var(--muted-text); margin-bottom: 20px;"></i>
<h4 style="margin-bottom: 10px; font-size: 1.3rem;">Authentication Required</h4>
<p style="color: var(--muted-text); margin-bottom: 25px; line-height: 1.6;">
To maintain the exclusivity of our inventory and prevent fraudulent requests, we require all
prospective buyers to authenticate their identity before initiating an acquisition
procedure.
</p>
<div style="display: flex; gap: 15px; justify-content: center;">
<a href="login.php" class="btn btn-primary" style="padding: 12px 30px;">Login to Account</a>
<a href="register.php" class="btn btn-outline" style="padding: 12px 30px;">Apply for
Membership</a>
</div>
</div>
<?php endif; ?>
<?php else: ?>
<div class="btn btn-danger btn-block"
style="opacity: 0.7; cursor: not-allowed; font-size: 1.2rem; padding: 15px;">
<i class="fa-solid fa-lock"></i> Vehicle Sold
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</section>
<!-- Mobile Adjustments Setup using inline style since we map grid primarily -->
<style>
@media (max-width: 991px) {
section>.container>div[style*="grid-template-columns: 1fr 1fr"] {
grid-template-columns: 1fr !important;
}
}
</style>
<?php require_once 'footer.php'; ?>
<script>
const detailWishlistBtn = document.getElementById('detail-wishlist-btn');
if (detailWishlistBtn) {
detailWishlistBtn.addEventListener('click', function () {
const carId = this.dataset.carId;
const icon = this.querySelector('i');
const label = this.querySelector('span');
fetch('wishlist_action.php', {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: 'car_id=' + carId
})
.then(r => r.json())
.then(data => {
if (data.status === 'added') {
icon.className = 'fa-solid fa-heart';
this.style.color = '#f43f5e';
this.style.borderColor = '#f43f5e';
label.textContent = 'Saved';
} else {
icon.className = 'fa-regular fa-heart';
this.style.color = '';
this.style.borderColor = '';
label.textContent = 'Save';
}
});
});
}
</script>
