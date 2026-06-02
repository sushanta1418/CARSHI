document.addEventListener('DOMContentLoaded', () => {
// Mobile Menu Toggle
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navLinks = document.getElementById('navLinks');
if (mobileMenuBtn && navLinks) {
mobileMenuBtn.addEventListener('click', () => {
navLinks.classList.toggle('active');
const icon = mobileMenuBtn.querySelector('i');
if (navLinks.classList.contains('active')) {
icon.classList.remove('fa-bars');
icon.classList.add('fa-xmark');
} else {
icon.classList.remove('fa-xmark');
icon.classList.add('fa-bars');
}
});
}
// Scroll Animations (Intersection Observer)
const fadeElements = document.querySelectorAll('.animate-on-scroll');
if (fadeElements.length > 0) {
const appearOptions = {
threshold: 0.15,
rootMargin: "0px 0px -50px 0px"
};
const appearOnScroll = new IntersectionObserver(function (entries, observer) {
entries.forEach(entry => {
if (!entry.isIntersecting) {
return;
} else {
entry.target.classList.add('visible');
observer.unobserve(entry.target);
}
});
}, appearOptions);
fadeElements.forEach(element => {
appearOnScroll.observe(element);
});
}
// Navbar scroll effect
const navbar = document.querySelector('.navbar');
if (navbar) {
window.addEventListener('scroll', () => {
if (window.scrollY > 50) {
navbar.style.background = 'rgba(15, 23, 42, 0.95)';
navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.5)';
} else {
navbar.style.background = 'rgba(15, 23, 42, 0.9)';
navbar.style.boxShadow = 'none';
}
});
}
// Password Visibility Toggle
const togglePasswordIcons = document.querySelectorAll('.toggle-password');
togglePasswordIcons.forEach(icon => {
icon.addEventListener('click', function () {
const targetId = this.getAttribute('data-target');
const targetInput = document.getElementById(targetId);
if (targetInput) {
if (targetInput.type === 'password') {
targetInput.type = 'text';
this.classList.remove('fa-eye');
this.classList.add('fa-eye-slash');
} else {
targetInput.type = 'password';
this.classList.remove('fa-eye-slash');
this.classList.add('fa-eye');
}
}
});
});
// Form Validation (Password Match)
const registerForm = document.querySelector('form[action="register.php"]');
if (registerForm) {
registerForm.addEventListener('submit', function (e) {
const pass = document.getElementById('password');
const confirmPass = document.getElementById('confirm_password');
if (pass && confirmPass && pass.value !== confirmPass.value) {
e.preventDefault();
alert('Passwords do not match. Please try again.');
}
});
}
// Smooth Scrolling for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
anchor.addEventListener('click', function (e) {
const targetId = this.getAttribute('href');
if (targetId === '#') return;
const targetElement = document.querySelector(targetId);
if (targetElement) {
e.preventDefault();
targetElement.scrollIntoView({
behavior: 'smooth',
block: 'start'
});
}
});
});
// Dismissible Alerts
const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
// Create close button if not exists
if (!alert.querySelector('.dismiss-alert')) {
const closeBtn = document.createElement('button');
closeBtn.classList.add('dismiss-alert');
closeBtn.innerHTML = '&times;';
closeBtn.setAttribute('aria-label', 'Close alert');
alert.appendChild(closeBtn);
closeBtn.addEventListener('click', function () {
alert.style.opacity = '0';
setTimeout(() => {
alert.style.display = 'none';
}, 300);
});
}
});
// Back to Top Button
const backToTopBtn = document.getElementById('backToTopBtn');
if (backToTopBtn) {
window.addEventListener('scroll', () => {
if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
backToTopBtn.style.display = 'flex';
} else {
backToTopBtn.style.display = 'none';
}
});
backToTopBtn.addEventListener('click', () => {
window.scrollTo({ top: 0, behavior: 'smooth' });
});
}
});
