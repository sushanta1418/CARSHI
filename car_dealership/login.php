<?php
require_once 'init.php';
require_once 'auth.php';
// Redirect if already logged in
if (isLoggedIn()) {
if (isAdmin()) {
header("Location: admin.php");
} else {
header("Location: index.php");
}
exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (empty($email) || empty($password)) {
$error = 'Please enter both email and password.';
} else {
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
if ($user && password_verify($password, $user['password'])) {
loginUser($user['id'], $user['role']);
if ($user['role'] === 'admin') {
header("Location: admin.php");
} else {
header("Location: index.php");
}
exit;
} else {
$error = 'Invalid email or password.';
}
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - LuxeDrive Auto</title>
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
<h2>Welcome Back</h2>
<p>Sign in to your account to continue.</p>
</div>
<?php if ($error): ?>
<div class="alert alert-danger">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<form method="POST" action="login.php" class="auth-form">
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
<input type="password" id="password" name="password" required placeholder="Enter your password">
<i class="fa-solid fa-eye toggle-password" data-target="password"></i>
</div>
</div>
<button type="submit" class="btn btn-primary btn-block">Sign In</button>
</form>
<div class="auth-footer text-center">
<p>Don't have an account? <a href="register.php" class="text-primary">Sign up here</a></p>
<div style="margin-top: 15px; font-size: 0.9rem; color: #aaa;">
Admin Login Demo:<br>
Email: susantabhatta51@gmail.com<br>
Password: useradmin
</div>
<div style="margin-top: 20px;">
<a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
</div>
</div>
</div>
</div>
<script src="js/main.js"></script>
</body>
</html>
