<?php
// Wishlist page: shows all cars the logged-in user has saved, in reverse chronological order.
require_once 'init.php';
require_once 'auth.php';
// Require login — guests cannot have wishlists
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
require_once 'header.php';
// Fetch all wishlisted cars for the current user, joined with car details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
SELECT c.*, w.created_at AS saved_at
FROM wishlists w
JOIN cars c ON w.car_id = c.id
WHERE w.user_id = ?
ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlist_cars = $stmt->fetchAll();
?>
<!-- Wishlist Hero -->
<section
    style="padding: 120px 20px 60px; background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%); text-align: center;">
    <div class="container animate-on-scroll">
        <h1 style="font-size: 3rem; margin-bottom: 15px;">
            <i class="fa-solid fa-heart text-primary"></i> My Wishlist
        </h1>
        <p style="color: var(--muted-text); font-size: 1.1rem;">
            <?= count($wishlist_cars) ?> saved vehicle
            <?= count($wishlist_cars) !== 1 ? 's' : '' ?>
        </p>
    </div>
</section>
<section style="padding: 60px 20px;">
    <div class="container">
        <?php if (empty($wishlist_cars)): ?>
            <div class="glass-panel text-center animate-on-scroll" style="padding: 80px 40px;">
                <i class="fa-regular fa-heart" style="font-size: 5rem; color: var(--muted-text); margin-bottom: 25px;"></i>
                <h3 style="margin-bottom: 15px; font-size: 1.8rem;">No saved vehicles yet</h3>
                <p style="color: var(--muted-text); margin-bottom: 30px;">Browse our inventory and click the heart icon to
                    save cars you love.</p>
                <a href="cars.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 1.1rem;">
                    <i class="fa-solid fa-car"></i> Browse Inventory
                </a>
            </div>
        <?php else: ?>
            <div class="cars-grid">
                <?php foreach ($wishlist_cars as $car): ?>
                    <div class="car-card animate-on-scroll">
                        <div class="car-image-container">
                            <img src="<?= htmlspecialchars($car['image_url'] ?? 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') ?>"
                                alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" class="car-image">
                            <?php if ($car['status'] === 'sold'): ?>
                                <span class="car-badge sold">SOLD</span>
                            <?php else: ?>
                                <span class="car-badge">AVAILABLE</span>
                            <?php endif; ?>
                            <!-- Heart toggle (already wishlisted) -->
                            <button class="wishlist-btn wishlisted" data-car-id="<?= $car['id'] ?>" title="Remove from wishlist"
                                style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,0.6);border:none;border-radius:50%;width:38px;height:38px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;">
                                <i class="fa-solid fa-heart" style="color:#f43f5e;font-size:1rem;"></i>
                            </button>
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
                                    <?php $is_ev = ($car['fuel_type'] ?? '') === 'electric'; ?>
                                    <i class="fa-solid <?= $is_ev ? 'fa-bolt' : 'fa-gauge-high' ?> text-primary"></i>
                                    <?= number_format($car['mileage']) ?>         <?= $is_ev ? 'km range' : 'km/L' ?>
                                </div>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--muted-text); margin: 8px 0;">
                                <i class="fa-regular fa-bookmark"></i> Saved
                                <?= date('M j, Y', strtotime($car['saved_at'])) ?>
                            </p>
                            <div class="car-actions">
                                <a href="car_details.php?id=<?= $car['id'] ?>" class="btn btn-outline" style="flex:1;">
                                    <i class="fa-solid fa-eye"></i> Details
                                </a>
                                <?php if ($car['status'] !== 'sold'): ?>
                                    <a href="test_drive.php?car_id=<?= $car['id'] ?>" class="btn btn-primary" style="flex:1;">
                                        <i class="fa-solid fa-calendar-check"></i> Test Drive
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<script>
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const carId = this.dataset.carId;
            const card = this.closest('.car-card');
            fetch('wishlist_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'car_id=' + carId
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'removed') {
                        card.style.transition = 'all 0.4s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => card.remove(), 400);
                        // Update count heading
                        const heading = document.querySelector('section p[style*="muted-text"]');
                        if (heading) {
                            const n = parseInt(heading.textContent) - 1;
                            heading.textContent = n + ' saved vehicle' + (n !== 1 ? 's' : '');
                        }
                    }
                });
        });
    });
</script>
<?php require_once 'footer.php'; ?>