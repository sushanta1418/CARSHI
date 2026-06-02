<?php
// Admin panel: manages inventory, purchases, test drives, and users.
// Accessible only to users with the 'admin' role.
require_once 'init.php';
require_once 'auth.php';
requireAdmin(); // Redirect away if not an admin
$action = $_GET['action'] ?? 'dashboard';
$message = '';
$error = '';
// --- Handle all POST form actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Add a new vehicle to the inventory
if (isset($_POST['add_car'])) {
$make = trim($_POST['make']);
$model = trim($_POST['model']);
$year = (int) $_POST['year'];
$price = (float) $_POST['price'];
$mileage = (int) $_POST['mileage'];
$fuel_type = in_array($_POST['fuel_type'] ?? '', ['petrol', 'diesel', 'electric', 'hybrid']) ? $_POST['fuel_type'] : 'petrol';
$description = trim($_POST['description']);
$image_url = trim($_POST['image_url']);
if (empty($make) || empty($model) || empty($price)) {
$error = 'Make, Model, and Price are required.';
} else {
$stmt = $pdo->prepare("INSERT INTO cars (make, model, year, price, mileage, fuel_type, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$make, $model, $year, $price, $mileage, $fuel_type, $description, $image_url])) {
$message = 'Car added successfully.';
} else {
$error = 'Error adding car.';
}
}
// Update an existing vehicle's details
} elseif (isset($_POST['edit_car'])) {
$id = (int) $_POST['id'];
$make = trim($_POST['make']);
$model = trim($_POST['model']);
$year = (int) $_POST['year'];
$price = (float) $_POST['price'];
$mileage = (int) $_POST['mileage'];
$fuel_type = in_array($_POST['fuel_type'] ?? '', ['petrol', 'diesel', 'electric', 'hybrid']) ? $_POST['fuel_type'] : 'petrol';
$description = trim($_POST['description']);
$image_url = trim($_POST['image_url']);
$status = $_POST['status'] ?? 'available';
$stmt = $pdo->prepare("UPDATE cars SET make=?, model=?, year=?, price=?, mileage=?, fuel_type=?, description=?, image_url=?, status=? WHERE id=?");
if ($stmt->execute([$make, $model, $year, $price, $mileage, $fuel_type, $description, $image_url, $status, $id])) {
$message = 'Car updated successfully.';
} else {
$error = 'Error updating car.';
}
// Remove a vehicle from inventory
} elseif (isset($_POST['delete_car'])) {
$id = (int) $_POST['id'];
$stmt = $pdo->prepare("DELETE FROM cars WHERE id=?");
if ($stmt->execute([$id])) {
$message = 'Car deleted successfully.';
} else {
$error = 'Error deleting car.';
}
// Update a user's email, role, or password
} elseif (isset($_POST['edit_user'])) {
$id = (int) $_POST['id'];
$email = trim($_POST['email']);
$role = $_POST['role'];
$password = trim($_POST['password']);
if (!empty($password)) {
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET email=?, role=?, password=? WHERE id=?");
$success = $stmt->execute([$email, $role, $hashedPassword, $id]);
} else {
$stmt = $pdo->prepare("UPDATE users SET email=?, role=? WHERE id=?");
$success = $stmt->execute([$email, $role, $id]);
}
if ($success) {
$message = 'User updated successfully.';
} else {
$error = 'Error updating user.';
}
// Delete a user (admin accounts require a special password)
} elseif (isset($_POST['delete_user'])) {
$id = (int) $_POST['id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
$stmt->execute([$id]);
$userToDelete = $stmt->fetch();
if ($userToDelete) {
if ($userToDelete['role'] === 'admin') {
$admin_pass = $_POST['admin_pass'] ?? '';
if ($admin_pass !== '2345') {
$error = 'Incorrect special password. Admin not deleted.';
} else {
$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
if ($stmt->execute([$id])) {
$message = 'Admin user deleted successfully.';
} else {
$error = 'Error deleting user. They may have associated records.';
}
}
} else {
$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
if ($stmt->execute([$id])) {
$message = 'User deleted successfully.';
} else {
$error = 'Error deleting user. They may have associated records.';
}
}
} else {
$error = 'User not found.';
}
// Update the status of a test drive booking (pending/confirmed/done/cancelled)
} elseif (isset($_POST['update_td_status'])) {
$td_id = (int) ($_POST['td_id'] ?? 0);
$td_status = in_array($_POST['td_status'] ?? '', ['pending', 'confirmed', 'done', 'cancelled'])
? $_POST['td_status'] : 'pending';
try {
$stmt = $pdo->prepare("UPDATE test_drives SET status=? WHERE id=?");
if ($stmt->execute([$td_status, $td_id])) {
$message = 'Test drive status updated.';
} else {
$error = 'Error updating status.';
}
} catch (PDOException $e) {
$error = 'Error: ' . $e->getMessage();
}
// Update financing application status
} elseif (isset($_POST['update_financing_status'])) {
$fin_id = (int) ($_POST['fin_id'] ?? 0);
$fin_status = in_array($_POST['fin_status'] ?? '', ['pending', 'approved', 'rejected'])
? $_POST['fin_status'] : 'pending';
try {
$stmt = $pdo->prepare("UPDATE financing SET status=? WHERE id=?");
if ($stmt->execute([$fin_status, $fin_id])) {
$message = 'Financing application status updated.';
} else {
$error = 'Error updating financing status.';
}
} catch (PDOException $e) {
$error = 'Error: ' . $e->getMessage();
}
// Delete a financing application
} elseif (isset($_POST['delete_financing'])) {
$fin_id = (int) ($_POST['fin_id'] ?? 0);
try {
$stmt = $pdo->prepare("DELETE FROM financing WHERE id=?");
if ($stmt->execute([$fin_id])) {
$message = 'Financing application deleted.';
} else {
$error = 'Error deleting financing application.';
}
} catch (PDOException $e) {
$error = 'Error: ' . $e->getMessage();
}
}
}
// --- Fetch summary data for dashboard stats ---
$total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$available_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status='available'")->fetchColumn();
$sold_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status='sold'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$pending_drives = 0;
try {
$pending_drives = $pdo->query("SELECT COUNT(*) FROM test_drives WHERE status='pending'")->fetchColumn();
} catch (PDOException $e) { /* table may not exist yet */
}
$pending_financing = 0;
try {
$pending_financing = $pdo->query("SELECT COUNT(*) FROM financing WHERE status='pending'")->fetchColumn();
} catch (PDOException $e) { /* table may not exist yet */
}
// --- Fetch all records needed for the various admin views ---
$cars = $pdo->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll();
$purchases = $pdo->query("
SELECT p.*, u.email, c.make, c.model, c.year
FROM purchases p
JOIN users u ON p.user_id = u.id
JOIN cars c ON p.car_id = c.id
ORDER BY p.purchase_date DESC
")->fetchAll();
$all_users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
$test_drives = [];
try {
$test_drives = $pdo->query("
SELECT td.*, u.email, c.make, c.model, c.year
FROM test_drives td
JOIN users u ON td.user_id = u.id
JOIN cars c ON td.car_id = c.id
ORDER BY td.created_at DESC
")->fetchAll();
} catch (PDOException $e) { /* table may not exist yet */
}
$financings = [];
try {
$financings = $pdo->query("
SELECT f.*, u.email, c.make, c.model, c.year, c.price AS car_price
FROM financing f
JOIN users u ON f.user_id = u.id
JOIN cars c ON f.car_id = c.id
ORDER BY f.id DESC
")->fetchAll();
} catch (PDOException $e) { /* table may not exist yet */
}
require_once 'header.php';
?>
<div class="container" style="display: flex; gap: 30px; padding: 40px 20px;">
<!-- Sidebar -->
<aside style="width: 250px; flex-shrink: 0;" class="animate-on-scroll">
<div class="glass-panel" style="padding: 20px; position: sticky; top: 100px;">
<h3 style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Admin
Menu</h3>
<ul style="list-style: none; padding: 0;">
<li style="margin-bottom: 10px;">
<a href="?action=dashboard"
class="btn <?= $action === 'dashboard' ? 'btn-primary' : 'btn-outline' ?> btn-block"
style="text-align: left;"><i class="fa-solid fa-chart-line" style="width: 20px;"></i>
Dashboard</a>
</li>
<li style="margin-bottom: 10px;">
<a href="?action=inventory"
class="btn <?= $action === 'inventory' || $action === 'add_car' || $action === 'edit_car' ? 'btn-primary' : 'btn-outline' ?> btn-block"
style="text-align: left;"><i class="fa-solid fa-car" style="width: 20px;"></i> Inventory (
<?= $available_cars ?>)
</a>
</li>
<li style="margin-bottom: 10px;">
<a href="?action=purchases"
class="btn <?= $action === 'purchases' ? 'btn-primary' : 'btn-outline' ?> btn-block"
style="text-align: left;"><i class="fa-solid fa-file-invoice-dollar" style="width: 20px;"></i>
Purchases</a>
</li>
<li style="margin-bottom: 10px;">
<a href="?action=test_drives"
class="btn <?= $action === 'test_drives' ? 'btn-primary' : 'btn-outline' ?> btn-block"
style="text-align: left;"><i class="fa-solid fa-calendar-check" style="width: 20px;"></i>
Test
Drives<?= $pending_drives > 0 ? ' <span style="background:#f43f5e;color:#fff;border-radius:10px;padding:1px 7px;font-size:0.75rem;">' . $pending_drives . '</span>' : '' ?></a>
</li>
<li style="margin-bottom: 10px;">
<a href="?action=users"
class="btn <?= $action === 'users' || $action === 'edit_user' ? 'btn-primary' : 'btn-outline' ?> btn-block"
style="text-align: left;"><i class="fa-solid fa-users" style="width: 20px;"></i> Users
(<?= $total_users ?>)
</a>
</li>
<li style="margin-bottom: 10px;">
<a href="?action=financing"
class="btn <?= $action === 'financing' ? 'btn-primary' : 'btn-outline' ?> btn-block"
style="text-align: left;"><i class="fa-solid fa-file-contract" style="width: 20px;"></i>
Financing<?= $pending_financing > 0 ? ' <span style="background:#f43f5e;color:#fff;border-radius:10px;padding:1px 7px;font-size:0.75rem;">' . $pending_financing . '</span>' : '' ?></a>
</li>
<li>
<a href="index.php" class="btn btn-outline btn-block" style="text-align: left;"><i
class="fa-solid fa-store" style="width: 20px;"></i> View Store</a>
</li>
</ul>
</div>
</aside>
<!-- Main Content -->
<main style="flex: 1;" class="animate-on-scroll">
<?php if ($message): ?>
<div class="alert alert-success">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<?php if ($action === 'dashboard'): ?>
<h2 style="margin-bottom: 25px;">Dashboard Overview</h2>
<div
style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
<div class="glass-panel text-center" style="padding: 30px 20px;">
<i class="fa-solid fa-car text-primary" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
<h3 style="font-size: 2rem; margin-bottom: 5px;">
<?= $total_cars ?>
</h3>
<p style="color: var(--muted-text);">Total Vehicles</p>
</div>
<div class="glass-panel text-center" style="padding: 30px 20px;">
<i class="fa-solid fa-tags text-success" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
<h3 style="font-size: 2rem; margin-bottom: 5px;">
<?= $available_cars ?>
</h3>
<p style="color: var(--muted-text);">Available</p>
</div>
<div class="glass-panel text-center" style="padding: 30px 20px;">
<i class="fa-solid fa-hand-holding-dollar text-danger"
style="font-size: 2.5rem; margin-bottom: 15px;"></i>
<h3 style="font-size: 2rem; margin-bottom: 5px;">
<?= $sold_cars ?>
</h3>
<p style="color: var(--muted-text);">Sold</p>
</div>
<div class="glass-panel text-center" style="padding: 30px 20px;">
<i class="fa-solid fa-users text-primary" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
<h3 style="font-size: 2rem; margin-bottom: 5px;"><?= $total_users ?></h3>
<p style="color: var(--muted-text);">Registered Users</p>
</div>
<div class="glass-panel text-center" style="padding: 30px 20px;">
<i class="fa-solid fa-calendar-check"
style="font-size: 2.5rem; margin-bottom: 15px; color:#f43f5e;"></i>
<h3 style="font-size: 2rem; margin-bottom: 5px;"><?= $pending_drives ?></h3>
<p style="color: var(--muted-text);">Pending Test Drives</p>
</div>
<div class="glass-panel text-center" style="padding: 30px 20px;">
<i class="fa-solid fa-file-contract" style="font-size: 2.5rem; margin-bottom: 15px; color:#a78bfa;"></i>
<h3 style="font-size: 2rem; margin-bottom: 5px;"><?= $pending_financing ?></h3>
<p style="color: var(--muted-text);">Pending Financing</p>
</div>
</div>
<!-- Recent Purchases Brief -->
<div class="glass-panel" style="padding: 25px;">
<h3 style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Recent
Purchase Requests</h3>
<?php if (empty($purchases)): ?>
<p style="color: var(--muted-text);">No recent purchases.</p>
<?php else: ?>
<div class="table-responsive">
<table>
<thead>
<tr>
<th>Date</th>
<th>Customer</th>
<th>Vehicle</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach (array_slice($purchases, 0, 5) as $p): ?>
<tr>
<td>
<?= date('M j, Y H:i', strtotime($p['purchase_date'])) ?>
</td>
<td>
<?= htmlspecialchars($p['email']) ?>
</td>
<td>
<?= htmlspecialchars($p['year'] . ' ' . $p['make'] . ' ' . $p['model']) ?>
</td>
<td><span class="car-badge <?= $p['status'] === 'pending' ? '' : 'sold' ?>"
style="position:static;">
<?= ucfirst($p['status']) ?>
</span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php elseif ($action === 'inventory'): ?>
<div class="admin-header">
<h2>Manage Inventory</h2>
<a href="?action=add_car" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Vehicle</a>
</div>
<div class="glass-panel" style="padding: 25px;">
<?php if (empty($cars)): ?>
<p style="color: var(--muted-text);">No cars in inventory. Add some!</p>
<?php else: ?>
<div class="table-responsive">
<table>
<thead>
<tr>
<th>ID</th>
<th>Image</th>
<th>Vehicle</th>
<th>Price</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($cars as $car): ?>
<tr>
<td>
<?= $car['id'] ?>
</td>
<td>
<img src="<?= htmlspecialchars($car['image_url'] ?? 'https://via.placeholder.com/80x50/1e293b/fff?text=No+Image') ?>"
alt="car" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
</td>
<td>
<strong>
<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>
</strong><br>
<span style="font-size: 0.85rem; color: var(--muted-text);">
<?= htmlspecialchars($car['year']) ?> |
<?php $is_ev = ($car['fuel_type'] ?? '') === 'electric'; ?>
<?= number_format($car['mileage']) ?>             <?= $is_ev ? 'km range ⚡' : 'km/L' ?>
</span>
</td>
<td style="font-weight: bold; color: var(--primary);">Rs
<?= number_format($car['price']) ?>
</td>
<td>
<span class="car-badge <?= $car['status'] === 'sold' ? 'sold' : '' ?>"
style="position:static; padding: 4px 8px; font-size:0.75rem;">
<?= strtoupper($car['status']) ?>
</span>
</td>
<td>
<div style="display: flex; gap: 5px;">
<a href="?action=edit_car&id=<?= $car['id'] ?>" class="btn btn-sm btn-outline"><i
class="fa-solid fa-pen"></i></a>
<form method="POST" action="?action=inventory" style="display: inline;"
onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
<input type="hidden" name="id" value="<?= $car['id'] ?>">
<button type="submit" name="delete_car" class="btn btn-sm btn-danger"><i
class="fa-solid fa-trash"></i></button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php elseif ($action === 'add_car' || $action === 'edit_car'): ?>
<?php
$is_edit = ($action === 'edit_car');
$car = null;
if ($is_edit && isset($_GET['id'])) {
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$_GET['id']]);
$car = $stmt->fetch();
if (!$car) {
echo "<div class='alert alert-danger'>Car not found.</div>";
$is_edit = false;
}
}
?>
<div class="admin-header">
<h2>
<?= $is_edit ? 'Edit Vehicle: ' . htmlspecialchars($car['make'] . ' ' . $car['model']) : 'Add New Vehicle' ?>
</h2>
<a href="?action=inventory" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<div class="glass-panel" style="padding: 30px;">
<form method="POST" action="?action=inventory" class="auth-form"
style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
<?php if ($is_edit): ?>
<input type="hidden" name="id" value="<?= $car['id'] ?>">
<?php endif; ?>
<div class="form-group">
<label>Make</label>
<input type="text" name="make" class="form-control" required
value="<?= htmlspecialchars($car['make'] ?? '') ?>">
</div>
<div class="form-group">
<label>Model</label>
<input type="text" name="model" class="form-control" required
value="<?= htmlspecialchars($car['model'] ?? '') ?>">
</div>
<div class="form-group">
<label>Year</label>
<input type="number" name="year" class="form-control" required
value="<?= htmlspecialchars($car['year'] ?? date('Y')) ?>" min="1900"
max="<?= date('Y') + 1 ?>">
</div>
<div class="form-group">
<label>Price (Rs)</label>
<input type="number" name="price" class="form-control" required
value="<?= htmlspecialchars($car['price'] ?? '') ?>" step="0.01" min="0">
</div>
<div class="form-group">
<label>Fuel Efficiency (km/L) / Range (km)</label>
<input type="number" name="mileage" class="form-control" required
value="<?= htmlspecialchars($car['mileage'] ?? 0) ?>" min="0">
</div>
<div class="form-group">
<label>Fuel Type</label>
<select name="fuel_type" class="form-control"
style="background: var(--dark-bg); padding-left: 15px;">
<?php $ft = $car['fuel_type'] ?? 'petrol'; ?>
<option value="petrol" <?= $ft === 'petrol' ? 'selected' : '' ?>>⛽ Petrol</option>
<option value="diesel" <?= $ft === 'diesel' ? 'selected' : '' ?>>🛢️ Diesel</option>
<option value="hybrid" <?= $ft === 'hybrid' ? 'selected' : '' ?>>🔋 Hybrid</option>
<option value="electric" <?= $ft === 'electric' ? 'selected' : '' ?>>⚡ Electric</option>
</select>
</div>
<div class="form-group">
<label>Image URL</label>
<input type="url" name="image_url" class="form-control"
value="<?= htmlspecialchars($car['image_url'] ?? '') ?>"
placeholder="https://example.com/image.jpg">
</div>
<?php if ($is_edit): ?>
<div class="form-group">
<label>Status</label>
<select name="status" class="form-control" style="background: var(--dark-bg); padding-left: 15px;">
<option value="available" <?= $car['status'] === 'available' ? 'selected' : '' ?>>Available
</option>
<option value="sold" <?= $car['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
</select>
</div>
<?php endif; ?>
<div class="form-group" style="grid-column: 1 / -1;">
<label>Description</label>
<textarea name="description"
class="form-control"><?= htmlspecialchars($car['description'] ?? '') ?></textarea>
</div>
<div style="grid-column: 1 / -1; text-align: right; margin-top: 10px;">
<button type="submit" name="<?= $is_edit ? 'edit_car' : 'add_car' ?>" class="btn btn-primary"
style="padding: 12px 30px;">
<i class="fa-solid fa-save"></i>
<?= $is_edit ? 'Save Changes' : 'Add Vehicle' ?>
</button>
</div>
</form>
</div>
<?php elseif ($action === 'purchases'): ?>
<div class="admin-header">
<h2>Purchase Requests</h2>
</div>
<div class="glass-panel" style="padding: 25px;">
<?php if (empty($purchases)): ?>
<p style="color: var(--muted-text);">No purchase requests found.</p>
<?php else: ?>
<div class="table-responsive">
<table>
<thead>
<tr>
<th>ID</th>
<th>Date</th>
<th>Customer (Email)</th>
<th>Buyer Name</th>
<th>Phone</th>
<th>Vehicle</th>
<th>Payment</th>
<th>Delivery</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php foreach ($purchases as $p): ?>
<tr>
<td>#<?= $p['id'] ?></td>
<td><?= date('M j, Y H:i', strtotime($p['purchase_date'])) ?></td>
<td><a href="mailto:<?= htmlspecialchars($p['email']) ?>"
class="text-primary"><?= htmlspecialchars($p['email']) ?></a></td>
<td><?= htmlspecialchars($p['full_name'] ?? 'N/A') ?></td>
<td><?= htmlspecialchars($p['phone'] ?? 'N/A') ?></td>
<td>
<a href="car_details.php?id=<?= $p['car_id'] ?>" target="_blank"
style="text-decoration: underline;">
<?= htmlspecialchars($p['year'] . ' ' . $p['make'] . ' ' . $p['model']) ?>
</a>
</td>
<td><?= htmlspecialchars($p['payment_method'] ?? 'N/A') ?></td>
<td><?= htmlspecialchars($p['delivery_option'] ?? 'N/A') ?></td>
<td>
<span class="car-badge <?= $p['status'] === 'pending' ? '' : 'sold' ?>"
style="position:static;">
<?= strtoupper($p['status']) ?>
</span>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php elseif ($action === 'test_drives'): ?>
<div class="admin-header">
<h2><i class="fa-solid fa-calendar-check"></i> Test Drive Bookings</h2>
</div>
<div class="glass-panel" style="padding: 25px;">
<?php if (empty($test_drives)): ?>
<p style="color: var(--muted-text);">No test drive bookings yet.</p>
<?php else: ?>
<div class="table-responsive">
<table>
<thead>
<tr>
<th>ID</th>
<th>Booked On</th>
<th>Customer</th>
<th>Vehicle</th>
<th>Date & Time</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($test_drives as $td): ?>
<tr>
<td>#<?= $td['id'] ?></td>
<td><?= date('M j, Y', strtotime($td['created_at'])) ?></td>
<td>
<?= htmlspecialchars($td['full_name']) ?><br>
<span
style="font-size:0.8rem;color:var(--muted-text);"><?= htmlspecialchars($td['email']) ?></span>
</td>
<td><?= htmlspecialchars($td['year'] . ' ' . $td['make'] . ' ' . $td['model']) ?></td>
<td><?= htmlspecialchars(date('M j, Y', strtotime($td['preferred_date'])) . ' @ ' . $td['preferred_time']) ?>
</td>
<td>
<?php
$badge_color = match ($td['status']) {
'confirmed' => 'color:#22c55e;',
'done' => 'color:var(--muted-text);',
'cancelled' => 'color:#ef4444;',
default => 'color:var(--primary);'
};
?>
<span style="font-weight:600;<?= $badge_color ?>"><?= ucfirst($td['status']) ?></span>
</td>
<td>
<form method="POST" action="?action=test_drives" style="display:flex;gap:5px;">
<input type="hidden" name="td_id" value="<?= $td['id'] ?>">
<select name="td_status" class="form-control"
style="background:var(--dark-bg);padding:4px 8px;font-size:0.8rem;width:auto;">
<?php foreach (['pending', 'confirmed', 'done', 'cancelled'] as $s): ?>
<option value="<?= $s ?>" <?= $td['status'] === $s ? 'selected' : '' ?>>
<?= ucfirst($s) ?>
</option>
<?php endforeach; ?>
</select>
<button type="submit" name="update_td_status"
class="btn btn-sm btn-primary">Save</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php elseif ($action === 'users'): ?>
<div class="admin-header">
<h2>Manage Users</h2>
</div>
<div class="glass-panel" style="padding: 25px;">
<div class="table-responsive">
<table>
<thead>
<tr>
<th>ID</th>
<th>Email</th>
<th>Role</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($all_users as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td>
<span class="car-badge <?= $u['role'] === 'admin' ? '' : 'sold' ?>"
style="position:static; padding: 4px 8px; font-size:0.75rem;">
<?= strtoupper($u['role']) ?>
</span>
</td>
<td>
<div style="display: flex; gap: 5px;">
<a href="?action=edit_user&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline"><i
class="fa-solid fa-pen"></i></a>
<form method="POST" action="?action=users" style="display: inline;"
onsubmit="handleDelete(event, this, '<?= $u['role'] ?>');">
<input type="hidden" name="id" value="<?= $u['id'] ?>">
<input type="hidden" name="admin_pass" value="">
<input type="hidden" name="delete_user" value="1">
<button type="submit" class="btn btn-sm btn-danger"><i
class="fa-solid fa-trash"></i></button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php elseif ($action === 'edit_user'): ?>
<?php
$user_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$edit_user = $stmt->fetch();
if (!$edit_user):
echo "<div class='alert alert-danger'>User not found.</div>";
else:
?>
<div class="admin-header">
<h2>Edit User: <?= htmlspecialchars($edit_user['email']) ?></h2>
<a href="?action=users" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<div class="glass-panel" style="padding: 30px;">
<form method="POST" action="?action=users" class="auth-form" style="max-width: 600px;">
<input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
<div class="form-group">
<label>Email Address</label>
<input type="email" name="email" class="form-control" required
value="<?= htmlspecialchars($edit_user['email']) ?>">
</div>
<div class="form-group">
<label>Role</label>
<select name="role" class="form-control" style="background: var(--dark-bg); padding-left: 15px;">
<option value="user" <?= $edit_user['role'] === 'user' ? 'selected' : '' ?>>User</option>
<option value="admin" <?= $edit_user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
</select>
</div>
<div class="form-group">
<label>New Password (leave blank to keep current)</label>
<input type="password" name="password" class="form-control" placeholder="Enter new password">
</div>
<div style="text-align: right; margin-top: 20px;">
<button type="submit" name="edit_user" class="btn btn-primary" style="padding: 12px 30px;">
<i class="fa-solid fa-save"></i> Save Changes
</button>
</div>
</form>
</div>
<?php endif; ?>
<?php elseif ($action === 'financing'): ?>
<?php
// Mini summary counts for the financing panel header
$fin_total = count($financings);
$fin_pending = count(array_filter($financings, fn($f) => $f['status'] === 'pending'));
$fin_approved = count(array_filter($financings, fn($f) => $f['status'] === 'approved'));
$fin_rejected = count(array_filter($financings, fn($f) => $f['status'] === 'rejected'));
?>
<div class="admin-header">
<h2><i class="fa-solid fa-file-contract"></i> Financing Applications</h2>
</div>
<!-- Summary ribbon -->
<div style="display:flex;gap:15px;margin-bottom:20px;flex-wrap:wrap;">
<div class="glass-panel" style="padding:15px 22px;flex:1;min-width:120px;text-align:center;">
<div style="font-size:1.6rem;font-weight:700;color:var(--light-text);"><?= $fin_total ?></div>
<div style="font-size:0.78rem;color:var(--muted-text);text-transform:uppercase;letter-spacing:.05em;">
Total</div>
</div>
<div class="glass-panel" style="padding:15px 22px;flex:1;min-width:120px;text-align:center;">
<div style="font-size:1.6rem;font-weight:700;color:var(--primary);"><?= $fin_pending ?></div>
<div style="font-size:0.78rem;color:var(--muted-text);text-transform:uppercase;letter-spacing:.05em;">
Pending</div>
</div>
<div class="glass-panel" style="padding:15px 22px;flex:1;min-width:120px;text-align:center;">
<div style="font-size:1.6rem;font-weight:700;color:#22c55e;"><?= $fin_approved ?></div>
<div style="font-size:0.78rem;color:var(--muted-text);text-transform:uppercase;letter-spacing:.05em;">
Approved</div>
</div>
<div class="glass-panel" style="padding:15px 22px;flex:1;min-width:120px;text-align:center;">
<div style="font-size:1.6rem;font-weight:700;color:#ef4444;"><?= $fin_rejected ?></div>
<div style="font-size:0.78rem;color:var(--muted-text);text-transform:uppercase;letter-spacing:.05em;">
Rejected</div>
</div>
</div>
<div class="glass-panel" style="padding: 25px;">
<?php if (empty($financings)): ?>
<div style="text-align:center;padding:40px 20px;">
<i class="fa-solid fa-folder-open"
style="font-size:3rem;color:var(--muted-text);margin-bottom:15px;"></i>
<p style="color:var(--muted-text);font-size:1.05rem;">No financing applications found.</p>
</div>
<?php else: ?>
<div class="table-responsive">
<table>
<thead>
<tr>
<th>#</th>
<th>Customer</th>
<th>Vehicle</th>
<th>Down Payment</th>
<th>Term</th>
<th>Status</th>
<th style="text-align:right;">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($financings as $fin): ?>
<?php
[$fin_bg, $fin_col] = match ($fin['status']) {
'approved' => ['rgba(34,197,94,.15)', '#22c55e'],
'rejected' => ['rgba(239,68,68,.15)', '#ef4444'],
default => ['rgba(99,102,241,.15)', 'var(--primary)']
};
?>
<tr>
<td style="font-weight:600;color:var(--muted-text);">#<?= $fin['id'] ?></td>
<td>
<a href="mailto:<?= htmlspecialchars($fin['email']) ?>" class="text-primary"
style="font-weight:600;">
<?= htmlspecialchars($fin['email']) ?>
</a>
</td>
<td>
<a href="car_details.php?id=<?= $fin['car_id'] ?>" target="_blank"
style="font-weight:600;text-decoration:none;">
<?= htmlspecialchars($fin['year'] . ' ' . $fin['make'] . ' ' . $fin['model']) ?>
</a><br>
<span style="font-size:0.8rem;color:var(--muted-text);">Rs
<?= number_format($fin['car_price']) ?></span>
</td>
<td style="font-weight:600;">Rs <?= number_format($fin['down_payment']) ?></td>
<td><?= $fin['term_months'] ?> mo.</td>
<td>
<span
style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:700;background:<?= $fin_bg ?>;color:<?= $fin_col ?>;letter-spacing:.04em;text-transform:uppercase;">
<?= ucfirst($fin['status']) ?>
</span>
</td>
<td>
<div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
<!-- Status update form -->
<form method="POST" action="?action=financing"
style="display:flex;gap:5px;align-items:center;">
<input type="hidden" name="fin_id" value="<?= $fin['id'] ?>">
<select name="fin_status" class="form-control"
style="background:var(--dark-bg);padding:4px 10px;font-size:0.8rem;width:auto;border-radius:6px;">
<?php foreach (['pending', 'approved', 'rejected'] as $s): ?>
<option value="<?= $s ?>" <?= $fin['status'] === $s ? 'selected' : '' ?>>
<?= ucfirst($s) ?>
</option>
<?php endforeach; ?>
</select>
<button type="submit" name="update_financing_status"
class="btn btn-sm btn-primary" title="Update status">
<i class="fa-solid fa-check"></i>
</button>
</form>
<!-- Delete form -->
<form method="POST" action="?action=financing" style="display:inline;"
onsubmit="return confirm('Delete this financing application? This cannot be undone.');">
<input type="hidden" name="fin_id" value="<?= $fin['id'] ?>">
<button type="submit" name="delete_financing" class="btn btn-sm btn-danger"
title="Delete application">
<i class="fa-solid fa-trash"></i>
</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
</main>
</div>
<!-- Simple Mobile Admin Layout adjustments -->
<style>
@media (max-width: 768px) {
.container {
flex-direction: column !important;
}
aside {
width: 100% !important;
}
aside .glass-panel {
position: static !important;
}
.auth-form {
grid-template-columns: 1fr !important;
}
}
</style>
<!-- Custom Admin Delete Modal -->
<div id="adminPasswordModal" class="modal-overlay"
style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(5px);">
<div class="glass-panel"
style="padding: 30px; width: 100%; max-width: 400px; text-align: center; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
<i class="fa-solid fa-shield-halved text-danger" style="font-size: 3rem; margin-bottom: 15px;"></i>
<h3 style="margin-bottom: 15px; color: var(--text-light);">Admin Authorization</h3>
<p style="color: var(--muted-text); margin-bottom: 20px; font-size: 0.95rem;">Please enter the special password
to authorize the deletion of this admin account.</p>
<input type="password" id="adminModalPassword" class="form-control" placeholder="Special Password"
style="margin-bottom: 20px; text-align: center;">
<div style="display: flex; gap: 10px; justify-content: center;">
<button type="button" class="btn btn-outline" onclick="closeAdminModal()">Cancel</button>
<button type="button" class="btn btn-danger" onclick="processAdminDelete()">Confirm Delete</button>
</div>
</div>
</div>
<script>
let currentDeleteForm = null;
function handleDelete(e, form, role) {
e.preventDefault();
if (role === 'admin') {
currentDeleteForm = form;
const modal = document.getElementById('adminPasswordModal');
modal.style.display = 'flex';
// Add a small delay for focus to work after display change
setTimeout(() => {
document.getElementById('adminModalPassword').focus();
}, 50);
} else {
if (confirm('Are you sure you want to delete this user?')) {
form.submit();
}
}
}
function processAdminDelete() {
const passInput = document.getElementById('adminModalPassword');
if (currentDeleteForm) {
currentDeleteForm.admin_pass.value = passInput.value;
currentDeleteForm.submit();
}
}
function closeAdminModal() {
document.getElementById('adminPasswordModal').style.display = 'none';
document.getElementById('adminModalPassword').value = '';
currentDeleteForm = null;
}
// Close modal on Escape key press
document.addEventListener('keydown', function (event) {
if (event.key === 'Escape') {
const modal = document.getElementById('adminPasswordModal');
if (modal.style.display === 'flex') {
closeAdminModal();
}
}
});
</script>
<?php require_once 'footer.php'; ?>
