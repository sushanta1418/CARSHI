<?php
// Test drive booking page: shown for a specific car, requires the user to be logged in.
require_once 'init.php';
require_once 'auth.php';
// Redirect to login if not authenticated; preserve the car_id so we can return after login
if (!isLoggedIn()) {
header("Location: login.php?redirect=test_drive.php" . (isset($_GET['car_id']) ? '?car_id=' . (int) $_GET['car_id'] : ''));
exit;
}
// Load the requested car — must exist and be available
$car_id = isset($_GET['car_id']) ? (int) $_GET['car_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status = 'available'");
$stmt->execute([$car_id]);
$car = $stmt->fetch();
// Redirect to inventory if the car is invalid or already sold
if (!$car) {
header("Location: cars.php");
exit;
}
$message = '';
$error = '';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$preferred_date = trim($_POST['preferred_date'] ?? '');
$preferred_time = trim($_POST['preferred_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');
// Basic validation
if (empty($full_name) || empty($phone) || empty($preferred_date) || empty($preferred_time)) {
$error = 'Please fill in all required fields.';
} elseif (strtotime($preferred_date) < strtotime('today')) {
$error = 'Preferred date cannot be in the past.';
} else {
$stmt = $pdo->prepare("INSERT INTO test_drives (user_id, car_id, full_name, phone, preferred_date, preferred_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$_SESSION['user_id'], $car_id, $full_name, $phone, $preferred_date, $preferred_time, $notes])) {
$message = "Your test drive request has been submitted! We'll confirm your booking shortly.";
} else {
$error = 'Something went wrong. Please try again.';
}
}
}
require_once 'header.php';
?>
<section style="padding: 100px 20px 60px;">
<div class="container" style="max-width: 720px; margin: 0 auto;">
<a href="car_details.php?id=<?= $car_id ?>" class="back-link"
style="display:inline-flex;align-items:center;gap:8px;margin-bottom:30px;">
<i class="fa-solid fa-arrow-left"></i> Back to
<?= htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']) ?>
</a>
<div class="glass-panel animate-on-scroll" style="padding: 40px;">
<div
style="display:flex;align-items:center;gap:16px;margin-bottom:30px;padding-bottom:24px;border-bottom:1px solid var(--border-color);">
<div
style="width:56px;height:56px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
<i class="fa-solid fa-car-on text-dark" style="font-size:1.5rem;color:#0f172a;"></i>
</div>
<div>
<h1 style="font-size:1.8rem;margin:0;">Book a Test Drive</h1>
<p style="color:var(--muted-text);margin:4px 0 0;">
<?= htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']) ?> &mdash; Rs
<?= number_format($car['price'], 2) ?>
</p>
</div>
</div>
<?php if ($message): ?>
<div class="alert alert-success" style="margin-bottom:25px;">
<i class="fa-solid fa-circle-check"></i>
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger" style="margin-bottom:25px;">
<i class="fa-solid fa-triangle-exclamation"></i>
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<?php if (!$message): ?>
<form method="POST" class="auth-form"
style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:100%;">
<div class="form-group">
<label for="full_name">Full Name *</label>
<input type="text" id="full_name" name="full_name" class="form-control" required
placeholder="John Doe" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
</div>
<div class="form-group">
<label for="phone">Phone Number *</label>
<input type="tel" id="phone" name="phone" class="form-control" required
placeholder="+977 98XXXXXXXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
</div>
<div class="form-group">
<label for="preferred_date">Preferred Date *</label>
<input type="date" id="preferred_date" name="preferred_date" class="form-control" required
min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['preferred_date'] ?? '') ?>">
</div>
<div class="form-group">
<label for="preferred_time">Preferred Time *</label>
<select id="preferred_time" name="preferred_time" class="form-control" required
style="background:var(--dark-bg);padding-left:15px;">
<option value="">-- Select Time --</option>
<option value="9:00 AM" <?= ($_POST['preferred_time'] ?? '') === '9:00 AM' ? 'selected' : '' ?>>
9:00 AM</option>
<option value="11:00 AM" <?= ($_POST['preferred_time'] ?? '') === '11:00 AM' ? 'selected' : '' ?>>
11:00 AM</option>
<option value="1:00 PM" <?= ($_POST['preferred_time'] ?? '') === '1:00 PM' ? 'selected' : '' ?>>
1:00 PM</option>
<option value="3:00 PM" <?= ($_POST['preferred_time'] ?? '') === '3:00 PM' ? 'selected' : '' ?>>
3:00 PM</option>
<option value="5:00 PM" <?= ($_POST['preferred_time'] ?? '') === '5:00 PM' ? 'selected' : '' ?>>
5:00 PM</option>
</select>
</div>
<div class="form-group" style="grid-column:1/-1;">
<label for="notes">Additional Notes <span
style="color:var(--muted-text);font-weight:400;">(optional)</span></label>
<textarea id="notes" name="notes" class="form-control" rows="3"
placeholder="Any special requests or questions..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
</div>
<div style="grid-column:1/-1;">
<button type="submit" class="btn btn-primary btn-block"
style="font-size:1.1rem;padding:14px;display:flex;align-items:center;justify-content:center;gap:10px;">
<i class="fa-solid fa-calendar-check"></i> Confirm Test Drive Request
</button>
</div>
</form>
<?php else: ?>
<div style="text-align:center;padding:20px 0;">
<a href="cars.php" class="btn btn-outline" style="margin-right:10px;"><i class="fa-solid fa-car"></i>
Browse More Cars</a>
<a href="car_details.php?id=<?= $car_id ?>" class="btn btn-primary"><i class="fa-solid fa-eye"></i> Back
to Car</a>
</div>
<?php endif; ?>
</div>
</div>
</section>
<?php require_once 'footer.php'; ?>
