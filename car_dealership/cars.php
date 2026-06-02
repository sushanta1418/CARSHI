<?php
// Cars listing page: supports keyword search, price/year filtering, sort, and pagination.
require_once 'init.php';
// --- Pagination ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;
// --- Filter inputs from query string ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : '';
$min_year = isset($_GET['min_year']) && $_GET['min_year'] !== '' ? (int) $_GET['min_year'] : '';
$max_year = isset($_GET['max_year']) && $_GET['max_year'] !== '' ? (int) $_GET['max_year'] : '';
// --- Build the WHERE clause dynamically from active filters ---
$query_parts = ["status = 'available'"];
$params = [];
if ($search) {
$query_parts[] = "(make LIKE :search OR model LIKE :search OR year LIKE :search)";
$params[':search'] = '%' . $search . '%';
}
if ($min_price !== '') {
$query_parts[] = "price >= :min_price";
$params[':min_price'] = $min_price;
}
if ($max_price !== '') {
$query_parts[] = "price <= :max_price";
$params[':max_price'] = $max_price;
}
if ($min_year !== '') {
$query_parts[] = "year >= :min_year";
$params[':min_year'] = $min_year;
}
if ($max_year !== '') {
$query_parts[] = "year <= :max_year";
$params[':max_year'] = $max_year;
}
$where_clause = implode(' AND ', $query_parts);
// --- Sort direction ---
$order_by = "id DESC"; // default: newest first
if ($sort === 'price_asc')
$order_by = "price ASC";
if ($sort === 'price_desc')
$order_by = "price DESC";
if ($sort === 'year_desc')
$order_by = "year DESC";
// --- Count total matching cars for pagination ---
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE $where_clause");
$stmt_count->execute($params);
$total_cars = $stmt_count->fetchColumn();
$total_pages = ceil($total_cars / $limit);
// --- Fetch the current page of cars ---
$sql = "SELECT * FROM cars WHERE $where_clause ORDER BY $order_by LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => &$val) {
$stmt->bindParam($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cars = $stmt->fetchAll();
require_once 'header.php';
// --- Load this user's wishlist IDs so we can highlight saved cars ---
$user_wishlist_ids = [];
if (isLoggedIn()) {
$wl = $pdo->prepare("SELECT car_id FROM wishlists WHERE user_id = ?");
$wl->execute([$_SESSION['user_id']]);
$user_wishlist_ids = array_column($wl->fetchAll(), 'car_id');
}
?>
<!-- Header Section -->
<section
style="padding: 120px 20px 60px; background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%); text-align: center;">
<div class="container animate-on-scroll">
<h1 style="font-size: 3rem; margin-bottom: 20px; color: var(--light-text);">Premium Inventory</h1>
<p style="color: var(--muted-text); font-size: 1.2rem; max-width: 600px; margin: 0 auto;">
Explore our meticulously curated selection of exceptional vehicles.
</p>
</div>
</section>
<!-- Filter Section -->
<section
style="padding: 30px 20px; background: rgba(30, 41, 59, 0.5); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
<div class="container">
<form method="GET" action="cars.php" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
<!-- Keyword Search -->
<div style="flex: 2; min-width: 220px;">
<label
style="color: var(--muted-text); font-size: 0.8rem; display:block; margin-bottom:6px;">SEARCH</label>
<div class="input-with-icon">
<i class="fa-solid fa-magnifying-glass"></i>
<input type="text" name="search" placeholder="Make, model, year..."
value="<?= htmlspecialchars($search) ?>" class="form-control"
style="background: var(--dark-bg);">
</div>
</div>
<!-- Price Range -->
<div style="flex: 1; min-width: 130px;">
<label style="color: var(--muted-text); font-size: 0.8rem; display:block; margin-bottom:6px;">MIN PRICE
(Rs)</label>
<input type="number" name="min_price" placeholder="0" min="0" step="1000"
value="<?= htmlspecialchars($min_price) ?>" class="form-control"
style="background: var(--dark-bg);">
</div>
<div style="flex: 1; min-width: 130px;">
<label style="color: var(--muted-text); font-size: 0.8rem; display:block; margin-bottom:6px;">MAX PRICE
(Rs)</label>
<input type="number" name="max_price" placeholder="Any" min="0" step="1000"
value="<?= htmlspecialchars($max_price) ?>" class="form-control"
style="background: var(--dark-bg);">
</div>
<!-- Year Range -->
<div style="flex: 1; min-width: 110px;">
<label style="color: var(--muted-text); font-size: 0.8rem; display:block; margin-bottom:6px;">MIN
YEAR</label>
<input type="number" name="min_year" placeholder="2000" min="1900" max="<?= date('Y') + 1 ?>"
value="<?= htmlspecialchars($min_year) ?>" class="form-control" style="background: var(--dark-bg);">
</div>
<div style="flex: 1; min-width: 110px;">
<label style="color: var(--muted-text); font-size: 0.8rem; display:block; margin-bottom:6px;">MAX
YEAR</label>
<input type="number" name="max_year" placeholder="<?= date('Y') + 1 ?>" min="1900"
max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($max_year) ?>" class="form-control"
style="background: var(--dark-bg);">
</div>
<!-- Sort + Submit -->
<div style="flex: 1; min-width: 160px;">
<label for="sort"
style="color: var(--muted-text); font-size: 0.8rem; display:block; margin-bottom:6px;">SORT
BY</label>
<select name="sort" id="sort" class="form-control"
style="width: 100%; background: var(--dark-bg); padding-left: 15px;">
<option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Newest Arrivals</option>
<option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price ↑</option>
<option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price ↓</option>
<option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Year (Newest)</option>
</select>
</div>
<div style="display: flex; gap: 10px; align-items: flex-end;">
<button type="submit" class="btn btn-primary" style="white-space:nowrap;">
<i class="fa-solid fa-filter"></i> Filter
</button>
<?php if ($search || $min_price !== '' || $max_price !== '' || $min_year !== '' || $max_year !== ''): ?>
<a href="cars.php" class="btn btn-outline" style="white-space:nowrap;">
<i class="fa-solid fa-xmark"></i> Clear
</a>
<?php endif; ?>
</div>
</form>
</div>
</section>
<!-- Inventory Grid -->
<section style="padding: 60px 20px;">
<div class="container">
<div style="margin-bottom: 30px; color: var(--muted-text);">
Showing
<?= count($cars) ?> of
<?= $total_cars ?> vehicles
</div>
<div class="cars-grid">
<?php if (empty($cars)): ?>
<div class="text-center glass-panel" style="grid-column: 1 / -1; padding: 60px 20px;">
<i class="fa-solid fa-car-tunnel"
style="font-size: 4rem; color: var(--muted-text); margin-bottom: 20px;"></i>
<h3>No vehicles found</h3>
<p style="color: var(--muted-text); margin-top: 10px;">Try adjusting your search criteria.</p>
<?php if ($search || $sort !== 'latest'): ?>
<a href="cars.php" class="btn btn-outline" style="margin-top: 20px;">Clear Filters</a>
<?php endif; ?>
</div>
<?php else: ?>
<?php foreach ($cars as $car): ?>
<div class="car-card animate-on-scroll">
<div class="car-image-container">
<img src="<?= htmlspecialchars($car['image_url'] ?? 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') ?>"
alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" class="car-image">
<?php if ($car['status'] === 'sold'): ?>
<span class="car-badge sold">SOLD</span>
<?php else: ?>
<span class="car-badge">AVAILABLE</span>
<?php endif; ?>
<?php if (isLoggedIn() && !isAdmin()): ?>
<?php $is_wishlisted = in_array($car['id'], $user_wishlist_ids); ?>
<button class="wishlist-btn <?= $is_wishlisted ? 'wishlisted' : '' ?>"
data-car-id="<?= $car['id'] ?>"
title="<?= $is_wishlisted ? 'Remove from wishlist' : 'Save to wishlist' ?>"
style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,0.6);border:none;border-radius:50%;width:38px;height:38px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;">
<i class="<?= $is_wishlisted ? 'fa-solid' : 'fa-regular' ?> fa-heart"
style="color:<?= $is_wishlisted ? '#f43f5e' : '#fff' ?>;font-size:1rem;"></i>
</button>
<?php endif; ?>
</div>
<div class="car-info">
<h3 class="car-title">
<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>
</h3>
<div class="car-price">Rs<?= number_format($car['price'], 2) ?></div>
<div class="car-details">
<div class="car-detail-item">
<i class="fa-regular fa-calendar text-primary"></i> <?= htmlspecialchars($car['year']) ?>
</div>
<div class="car-detail-item">
<?php $is_ev = ($car['fuel_type'] ?? '') === 'electric'; ?>
<i class="fa-solid <?= $is_ev ? 'fa-bolt' : 'fa-gauge-high' ?> text-primary"></i>
<?= number_format($car['mileage']) ?>         <?= $is_ev ? 'km range' : 'km/L' ?>
</div>
</div>
<div class="car-actions">
<a href="car_details.php?id=<?= $car['id'] ?>" class="btn btn-outline" style="flex:1;"><i
class="fa-solid fa-eye"></i> Details</a>
<?php if ($car['status'] !== 'sold'): ?>
<a href="test_drive.php?car_id=<?= $car['id'] ?>" class="btn btn-outline" style="flex:1;"
title="Book Test Drive"><i class="fa-solid fa-calendar-check"></i> Test Drive</a>
<a href="car_details.php?id=<?= $car['id'] ?>#buy" class="btn btn-primary" style="flex:1;"><i
class="fa-solid fa-cart-shopping"></i> Buy Now</a>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<!-- Pagination -->
<?php
$pag_qs = http_build_query(array_filter([
'search' => $search,
'sort' => $sort,
'min_price' => $min_price,
'max_price' => $max_price,
'min_year' => $min_year,
'max_year' => $max_year,
], fn($v) => $v !== ''));
?>
<?php if ($total_pages > 1): ?>
<div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;" class="animate-on-scroll">
<?php if ($page > 1): ?>
<a href="?page=<?= $page - 1 ?>&<?= $pag_qs ?>" class="btn btn-outline"><i
class="fa-solid fa-angle-left"></i> Prev</a>
<?php endif; ?>
<?php for ($i = 1; $i <= $total_pages; $i++): ?>
<a href="?page=<?= $i ?>&<?= $pag_qs ?>" class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"
style="padding: 10px 15px;"><?= $i ?></a>
<?php endfor; ?>
<?php if ($page < $total_pages): ?>
<a href="?page=<?= $page + 1 ?>&<?= $pag_qs ?>" class="btn btn-outline">Next <i
class="fa-solid fa-angle-right"></i></a>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</section>
<?php require_once 'footer.php'; ?>
<script>
document.querySelectorAll('.wishlist-btn').forEach(btn => {
btn.addEventListener('click', function () {
const carId = this.dataset.carId;
const icon = this.querySelector('i');
fetch('wishlist_action.php', {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: 'car_id=' + carId
})
.then(r => r.json())
.then(data => {
if (data.status === 'added') {
icon.className = 'fa-solid fa-heart';
icon.style.color = '#f43f5e';
this.classList.add('wishlisted');
this.title = 'Remove from wishlist';
} else {
icon.className = 'fa-regular fa-heart';
icon.style.color = '#fff';
this.classList.remove('wishlisted');
this.title = 'Save to wishlist';
}
})
.catch(() => { window.location.href = 'login.php'; });
});
});
</script>
