<?php
require_once 'init.php';
require_once 'header.php';
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$inquiry = trim($_POST['inquiry'] ?? '');
if (!empty($name) && !empty($email) && !empty($inquiry)) {
// In a real application, this would send an email or save to DB.
// For this project, we'll simulate a successful submission.
$message = "Thank you for reaching out, $name. Our team will contact you shortly.";
} else {
$error = "Please fill in all required fields.";
}
}
?>
<!-- Hero Section -->
<section
style="padding: 120px 20px 60px; background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%); text-align: center;">
<div class="container animate-on-scroll">
<h1 style="font-size: 3.5rem; margin-bottom: 20px; color: var(--primary);">Contact Us</h1>
<p style="color: var(--muted-text); font-size: 1.3rem; max-width: 600px; margin: 0 auto;">
We're here to assist you with any inquiries regarding our premium vehicle selection.
</p>
</div>
</section>
<section style="padding: 80px 20px;">
<div class="container">
<?php if ($message): ?>
<div class="alert alert-success animate-on-scroll" style="max-width: 800px; margin: 0 auto 40px;">
<i class="fa-solid fa-check-circle"></i>
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if (isset($error)): ?>
<div class="alert alert-danger animate-on-scroll" style="max-width: 800px; margin: 0 auto 40px;">
<i class="fa-solid fa-triangle-exclamation"></i>
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
<!-- Contact Information -->
<div class="animate-on-scroll">
<h2 style="font-size: 2.5rem; margin-bottom: 30px; color: var(--light-text);">Get In Touch</h2>
<div style="margin-bottom: 40px;">
<div style="display: flex; gap: 20px; margin-bottom: 25px;">
<div
style="width: 50px; height: 50px; background: rgba(240, 193, 75, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
<i class="fa-solid fa-location-dot text-primary" style="font-size: 1.5rem;"></i>
</div>
<div>
<h3 style="margin-bottom: 5px; font-size: 1.2rem;">Location</h3>
<p style="color: var(--muted-text); line-height: 1.6;">123 Elite Avenue<br>Luxury Park,
Kathmandu<br>Nepal</p>
</div>
</div>
<div style="display: flex; gap: 20px; margin-bottom: 25px;">
<div
style="width: 50px; height: 50px; background: rgba(240, 193, 75, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
<i class="fa-solid fa-phone text-primary" style="font-size: 1.5rem;"></i>
</div>
<div>
<h3 style="margin-bottom: 5px; font-size: 1.2rem;">Phone & Direct Lines</h3>
<p style="color: var(--muted-text); line-height: 1.6;">Main: +977 9824408947<br>Sales: +977
9824408947<br>Service: +977 9824408947</p>
</div>
</div>
<div style="display: flex; gap: 20px; margin-bottom: 25px;">
<div
style="width: 50px; height: 50px; background: rgba(240, 193, 75, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
<i class="fa-solid fa-envelope text-primary" style="font-size: 1.5rem;"></i>
</div>
<div>
<h3 style="margin-bottom: 5px; font-size: 1.2rem;">Email Us</h3>
<p style="color: var(--muted-text); line-height: 1.6;">
susantabhatta51@gmail.com<br>sushantabhatta6@gmail.com</p>
</div>
</div>
</div>
<h3
style="margin-bottom: 15px; font-size: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 30px;">
Business Hours</h3>
<ul style="list-style: none; padding: 0; color: var(--muted-text);">
<li
style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
<span>Sunday - Thursday</span> <span>9:00 AM - 7:00 PM</span>
</li>
<li
style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
<span>Friday</span> <span>10:00 AM - 5:00 PM</span>
</li>
<li style="display: flex; justify-content: space-between;">
<span>Saturday</span> <span class="text-primary">Closed</span>
</li>
</ul>
</div>
<!-- Contact Form -->
<div class="glass-panel animate-on-scroll" style="padding: 40px;">
<h3 style="margin-bottom: 30px; font-size: 1.8rem;">Send an Inquiry</h3>
<form method="POST" action="contact.php" class="auth-form">
<div class="form-group">
<label for="name">Full Name *</label>
<input type="text" id="name" name="name" class="form-control" required placeholder="John Doe">
</div>
<div class="form-group">
<label for="email">Email Address *</label>
<input type="email" id="email" name="email" class="form-control" required
placeholder="johndoe@example.com">
</div>
<div class="form-group">
<label for="subject">Subject</label>
<select id="subject" name="subject" class="form-control"
style="background: var(--dark-bg); padding-left: 15px;">
<option value="general">General Inquiry</option>
<option value="sales">Sales & Test Drives</option>
<option value="finance">Financing Options</option>
<option value="service">Service & Maintenance</option>
</select>
</div>
<div class="form-group">
<label for="inquiry">Your Message *</label>
<textarea id="inquiry" name="inquiry" class="form-control" required
placeholder="How can we help you?" style="min-height: 150px;"></textarea>
</div>
<button type="submit" class="btn btn-primary btn-block" style="padding: 15px; font-size: 1.1rem;">
<i class="fa-solid fa-paper-plane" style="margin-right: 10px;"></i> Send Message
</button>
</form>
</div>
</div>
</div>
</section>
<!-- Mobile Adjustments Setup using inline style -->
<style>
@media (max-width: 991px) {
section>.container>div[style*="grid-template-columns: 1fr 1fr"] {
grid-template-columns: 1fr !important;
}
}
</style>
<?php require_once 'footer.php'; ?>
