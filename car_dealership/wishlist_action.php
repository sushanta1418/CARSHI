<?php
// Handles wishlist toggle (add/remove) via AJAX POST requests.
// Returns a JSON response with the action taken and the user's updated wishlist count.
require_once 'init.php';
require_once 'auth.php';
header('Content-Type: application/json');
// Only logged-in users can modify wishlists
if (!isLoggedIn()) {
http_response_code(401);
echo json_encode(['error' => 'Not logged in']);
exit;
}
$car_id = isset($_POST['car_id']) ? (int) $_POST['car_id'] : 0;
$user_id = $_SESSION['user_id'];
if (!$car_id) {
http_response_code(400);
echo json_encode(['error' => 'Invalid car']);
exit;
}
// Toggle: remove if already saved, add if not
$stmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND car_id = ?");
$stmt->execute([$user_id, $car_id]);
if ($stmt->fetch()) {
// Car is already wishlisted — remove it
$pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND car_id = ?")->execute([$user_id, $car_id]);
$status = 'removed';
} else {
// Car is not wishlisted — add it
$pdo->prepare("INSERT INTO wishlists (user_id, car_id) VALUES (?, ?)")->execute([$user_id, $car_id]);
$status = 'added';
}
// Return the new total wishlist count for the user
$count = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
$count->execute([$user_id]);
echo json_encode(['status' => $status, 'count' => (int) $count->fetchColumn()]);
?>
