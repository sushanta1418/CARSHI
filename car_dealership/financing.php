<?php
// Financing page: handles the pre-approval form submission and renders the payment calculator.
require_once 'init.php';
require_once 'header.php';
$message = '';
$error   = '';
// Handle pre-approval form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!isLoggedIn()) {
$error = "Please login or register to submit a financing application.";
} else {
$car_id       = (int) $_POST['car_id'];
$down_payment = (float) str_replace(',', '', $_POST['down_payment']);
$term         = (int) $_POST['term'];
$stmt = $pdo->prepare("INSERT INTO financing (user_id, car_id, down_payment, term_months) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$_SESSION['user_id'], $car_id, $down_payment, $term])) {
$message = "Your financing application has been successfully submitted. Our finance team will contact you shortly.";
} else {
$error = "There was an error processing your application. Please try again or contact support.";
}
}
}
// Fetch available cars for the vehicle dropdown and the calculator's price map
$cars         = $pdo->query("SELECT id, make, model, year, price FROM cars WHERE status = 'available' ORDER BY make, model")->fetchAll();
$selected_car = $_GET['car_id'] ?? null;
?>
<!-- Hero Section -->
<section
style="padding: 120px 20px 60px; background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%); text-align: center; position: relative;">
<div
style="position: absolute; top:0; left:0; width:100%; height:100%; background: url('https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; opacity: 0.15;">
</div>
<div class="container animate-on-scroll" style="position: relative; z-index: 1;">
<h1 style="font-size: 3.5rem; margin-bottom: 20px; color: var(--primary);">Bespoke Financing</h1>
<p style="color: var(--muted-text); font-size: 1.3rem; max-width: 600px; margin: 0 auto;">
Tailored financial solutions designed specifically for your next premium vehicle acquisition.
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
<?php if ($error): ?>
<div class="alert alert-danger animate-on-scroll" style="max-width: 800px; margin: 0 auto 40px;">
<i class="fa-solid fa-triangle-exclamation"></i>
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
<!-- Info Text -->
<div class="animate-on-scroll">
<h2 style="font-size: 2.5rem; margin-bottom: 25px; color: var(--light-text);">Why Finance With Us?</h2>
<p style="color: var(--muted-text); font-size: 1.1rem; line-height: 1.8; margin-bottom: 40px;">
We partner with a network of premium financial institutions to secure the most competitive rates and
flexible terms for our clientele. Whether you are purchasing a classic exotic or the latest luxury
SUV, our dedicated finance team ensures a seamless and discrete process.
</p>
<h3 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary);">Our Advantages</h3>
<ul style="list-style: none; padding: 0;">
<li style="display: flex; gap: 15px; margin-bottom: 20px;">
<i class="fa-solid fa-check text-success" style="font-size: 1.2rem; margin-top: 3px;"></i>
<div>
<strong style="display: block; font-size: 1.1rem; margin-bottom: 5px;">Competitive Interest
Rates</strong>
<p style="color: var(--muted-text);">Access to exclusionary tiered rates not available to
the general public.</p>
</div>
</li>
<li style="display: flex; gap: 15px; margin-bottom: 20px;">
<i class="fa-solid fa-check text-success" style="font-size: 1.2rem; margin-top: 3px;"></i>
<div>
<strong style="display: block; font-size: 1.1rem; margin-bottom: 5px;">Flexible
Terms</strong>
<p style="color: var(--muted-text);">Customized financing terms ranging from 24 to 84 months
based on the vehicle.</p>
</div>
</li>
<li style="display: flex; gap: 15px;">
<i class="fa-solid fa-check text-success" style="font-size: 1.2rem; margin-top: 3px;"></i>
<div>
<strong style="display: block; font-size: 1.1rem; margin-bottom: 5px;">White-Glove
Service</strong>
<p style="color: var(--muted-text);">A dedicated personal finance concierge from application
to final signature.</p>
</div>
</li>
</ul>
</div>
<!-- Pre-Approval Form -->
<div class="glass-panel animate-on-scroll" style="padding: 40px;">
<h3 style="margin-bottom: 20px; font-size: 1.8rem;">Apply for Pre-Approval</h3>
<p style="color: var(--muted-text); margin-bottom: 30px;">Complete this brief application to initiate
your bespoke financing profile.</p>
<?php if (!isLoggedIn()): ?>
<div class="alert alert-danger" style="margin-bottom: 20px;">
You must be logged in to submit a financing application.
</div>
<?php endif; ?>
<form method="POST" action="financing.php" class="auth-form">
<div class="form-group">
<label for="car_id">Interested Vehicle *</label>
<select id="car_id" name="car_id" class="form-control" required
style="background: var(--dark-bg); padding-left: 15px;">
<option value="">-- Select a Vehicle --</option>
<?php foreach ($cars as $car): ?>
<option value="<?= $car['id'] ?>" <?= $selected_car == $car['id'] ? 'selected' : '' ?>>
<?= htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']) ?> - Rs
<?= number_format($car['price']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label for="down_payment">Estimated Down Payment (Rs) *</label>
<input type="number" id="down_payment" name="down_payment" class="form-control" required min="0"
step="1000" placeholder="e.g., 20000">
</div>
<div class="form-group">
<label for="term">Desired Financing Term *</label>
<select id="term" name="term" class="form-control" required
style="background: var(--dark-bg); padding-left: 15px;">
<option value="24">24 Months</option>
<option value="36">36 Months</option>
<option value="48">48 Months</option>
<option value="60" selected>60 Months</option>
<option value="72">72 Months</option>
<option value="84">84 Months</option>
</select>
</div>
<hr style="border: 0; height: 1px; background: var(--border-color); margin: 30px 0;">
<button type="submit" class="btn btn-primary btn-block" style="padding: 15px; font-size: 1.1rem;"
<?= !isLoggedIn() ? 'disabled' : '' ?>>
<i class="fa-solid fa-file-signature" style="margin-right: 10px;"></i> Submit Application
</button>
<?php if (!isLoggedIn()): ?>
<div style="text-align: center; margin-top: 15px;">
<a href="login.php" class="text-primary" style="text-decoration: underline;">Login now to
apply</a>
</div>
<?php endif; ?>
</form>
</div>
</div>
</div>
</section>
<!-- Interactive Payment Calculator -->
<section id="calculator" style="padding: 60px 20px; background: rgba(0,0,0,0.2); border-top: 1px solid var(--border-color);">
<div class="container animate-on-scroll">
<div style="text-align: center; margin-bottom: 40px;">
<h2 style="font-size: 2rem; margin-bottom: 15px;">Payment Calculator</h2>
<p style="color: var(--muted-text);">Enter your details below to get an instant monthly payment estimate.</p>
</div>
<div class="glass-panel" style="max-width: 860px; margin: 0 auto; padding: 40px;">
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
<!-- Inputs Column -->
<div>
<div class="form-group">
<label for="calc_price">Vehicle Price (Rs)</label>
<input type="number" id="calc_price" class="form-control" min="0" step="10000"
placeholder="e.g. 12000000" oninput="calculatePayment()">
</div>
<div class="form-group">
<label for="calc_down">Down Payment (Rs)</label>
<input type="number" id="calc_down" class="form-control" min="0" step="10000"
placeholder="e.g. 2400000" oninput="calculatePayment()">
</div>
<div class="form-group">
<label for="calc_rate">Annual Interest Rate (%)</label>
<input type="number" id="calc_rate" class="form-control" min="0" max="50" step="0.1"
placeholder="e.g. 8.5" value="8.5" oninput="calculatePayment()">
</div>
<div class="form-group">
<label for="calc_term">Loan Term</label>
<select id="calc_term" class="form-control" style="background: var(--dark-bg); padding-left: 15px;" onchange="calculatePayment()">
<option value="24">24 Months (2 Years)</option>
<option value="36">36 Months (3 Years)</option>
<option value="48">48 Months (4 Years)</option>
<option value="60" selected>60 Months (5 Years)</option>
<option value="72">72 Months (6 Years)</option>
<option value="84">84 Months (7 Years)</option>
</select>
</div>
<button type="button" class="btn btn-primary btn-block" style="padding: 14px; font-size: 1.05rem;" onclick="calculatePayment()">
<i class="fa-solid fa-calculator" style="margin-right: 8px;"></i> Calculate
</button>
</div>
<!-- Results Column -->
<div id="calc_results" style="display: flex; flex-direction: column; justify-content: center; align-items: center; background: rgba(0,0,0,0.25); border-radius: 12px; padding: 30px; text-align: center; border: 1px solid var(--border-color);">
<p style="color: var(--muted-text); margin-bottom: 8px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em;">Est. Monthly Payment</p>
<div id="calc_monthly" style="font-size: 3rem; font-weight: 700; color: var(--primary); margin-bottom: 30px; line-height: 1;">--</div>
<div style="width: 100%; display: flex; flex-direction: column; gap: 14px; border-top: 1px solid var(--border-color); padding-top: 24px;">
<div style="display: flex; justify-content: space-between; font-size: 0.95rem;">
<span style="color: var(--muted-text);">Loan Amount</span>
<span id="calc_loan" style="font-weight: 600;">--</span>
</div>
<div style="display: flex; justify-content: space-between; font-size: 0.95rem;">
<span style="color: var(--muted-text);">Total Payment</span>
<span id="calc_total" style="font-weight: 600;">--</span>
</div>
<div style="display: flex; justify-content: space-between; font-size: 0.95rem;">
<span style="color: var(--muted-text);">Total Interest</span>
<span id="calc_interest" style="color: #f59e0b; font-weight: 600;">--</span>
</div>
</div>
</div>
</div>
<p style="font-size: 0.78rem; color: var(--muted-text); margin-top: 24px; opacity: 0.7; text-align: center;">
* Estimates are for illustrative purposes only and do not constitute a formal financing offer.
Actual rates depend on credit profile and lender approval.
</p>
</div>
</div>
</section>
<script>
// Car prices from PHP for auto-fill
const carPrices = {
<?php foreach ($cars as $car): ?>
<?= $car['id'] ?>: <?= $car['price'] ?>,
<?php endforeach; ?>
};
// Auto-fill calculator price when a car is selected in the form above
document.getElementById('car_id').addEventListener('change', function () {
const price = carPrices[this.value];
if (price) {
document.getElementById('calc_price').value = price;
calculatePayment();
// Smooth scroll to calculator
document.getElementById('calculator').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
});
function calculatePayment() {
const price    = parseFloat(document.getElementById('calc_price').value) || 0;
const down     = parseFloat(document.getElementById('calc_down').value)  || 0;
const annualRate = parseFloat(document.getElementById('calc_rate').value);
const termMonths = parseInt(document.getElementById('calc_term').value);
const loanAmount = Math.max(0, price - down);
document.getElementById('calc_loan').textContent = loanAmount > 0 ? 'Rs ' + loanAmount.toLocaleString('en-IN') : '--';
if (loanAmount <= 0 || isNaN(annualRate) || annualRate < 0 || termMonths <= 0) {
document.getElementById('calc_monthly').textContent  = '--';
document.getElementById('calc_total').textContent    = '--';
document.getElementById('calc_interest').textContent = '--';
return;
}
let monthly;
if (annualRate === 0) {
monthly = loanAmount / termMonths;
} else {
const r = (annualRate / 100) / 12;
monthly = loanAmount * (r * Math.pow(1 + r, termMonths)) / (Math.pow(1 + r, termMonths) - 1);
}
const totalPayment  = monthly * termMonths;
const totalInterest = totalPayment - loanAmount;
document.getElementById('calc_monthly').textContent  = 'Rs ' + Math.round(monthly).toLocaleString('en-IN');
document.getElementById('calc_total').textContent    = 'Rs ' + Math.round(totalPayment).toLocaleString('en-IN');
document.getElementById('calc_interest').textContent = 'Rs ' + Math.round(totalInterest).toLocaleString('en-IN');
}
</script>
<style>
@media (max-width: 991px) {
section>.container>div[style*="grid-template-columns: 1fr 1fr"] {
grid-template-columns: 1fr !important;
}
#calculator .glass-panel > div[style*="grid-template-columns: 1fr 1fr"] {
grid-template-columns: 1fr !important;
}
}
</style>
<?php require_once 'footer.php'; ?>
