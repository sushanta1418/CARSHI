<?php
require_once 'init.php';
require_once 'auth.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
if (empty($email) || empty($password) || empty($confirm_password)) {
$error = 'Please fill in all fields.';
} elseif ($password !== $confirm_password) {
$error = 'Passwords do not match.';
} else {
// Check if email exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) {
$error = 'Email is already registered.';
} else {
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'user')");
if ($stmt->execute([$email, $hashedPassword])) {
$success = 'Registration successful! You can now login.';
// Auto login after registration
$user_id = $pdo->lastInsertId();
loginUser($user_id, 'user');
header("Location: index.php");
exit;
} else {
$error = 'Registration failed. Please try again.';
}
}
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - LuxeDrive Auto</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap"
rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-mode auth-page">
<div class="auth-container">
<div class="auth-card glass-panel">
<div class="auth-header">
<a href="index.php" class="logo text-center">
<i class="fa-solid fa-car-side"></i> LuxeDrive
</a>
<h2>Create Account</h2>
<p>Join LuxeDrive to test drive and purchase premium vehicles.</p>
</div>
<?php if ($error): ?>
<div class="alert alert-danger">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success">
<?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<form method="POST" action="register.php" class="auth-form">
<div class="form-group">
<label for="email">Email Address</label>
<div class="input-with-icon">
<i class="fa-regular fa-envelope"></i>
<input type="email" id="email" name="email" required placeholder="name@example.com"
value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
</div>
</div>
<div class="form-group">
<label for="password">Password</label>
<div class="input-with-icon">
<i class="fa-solid fa-lock"></i>
<input type="password" id="password" name="password" required placeholder="Create a password">
<i class="fa-solid fa-eye toggle-password" data-target="password"></i>
</div>
</div>
<div class="form-group">
<label for="confirm_password">Confirm Password</label>
<div class="input-with-icon">
<i class="fa-solid fa-lock"></i>
<input type="password" id="confirm_password" name="confirm_password" required
placeholder="Confirm your password">
<i class="fa-solid fa-eye toggle-password" data-target="confirm_password"></i>
</div>
</div>
<button type="submit" class="btn btn-primary btn-block">Sign Up</button>
</form>
<div class="auth-footer text-center">
<p>Already have an account? <a href="login.php" class="text-primary">Login here</a></p>
<a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
</div>
</div>
</div>
<script src="js/main.js"></script>
</body>
</html>
