<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/img/logo.png">
    <title>Login - Employee Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.2.0/iconify.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-600 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Login Card -->
    <div class="bg-white rounded-lg shadow-xl p-8">
        <div class="text-center mb-8">
            <img
                src="/assets/img/logo.png"
                alt="Company Logo"
                class="mx-auto mb-5 h-16 w-auto object-contain"
            >
            <h2 class="text-3xl font-bold text-gray-800">Sign In</h2>
            <p class="text-gray-600 text-sm mt-2">Welcome back to Employee Management System</p>
        </div>

        <!-- Alert Messages -->
        <div id="alert" class="hidden mb-4 p-4 rounded text-sm font-medium"></div>

        <!-- Login Form -->
        <form id="loginForm">
            <!-- Email Field -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">
                    Email Address
                </label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 6.75h16v10.5H4V6.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="m5 8 7 5 7-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="w-full pl-11 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="you@example.com"
                        autocomplete="email"
                    >
                </div>
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-semibold mb-2">
                    Password
                </label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M6 10h12v9H6v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full pl-11 pr-11 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <button
                        type="button"
                        id="togglePassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <span class="iconify" data-icon="mdi:eye"></span>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center mb-6">
                <label class="flex items-center text-gray-700 cursor-pointer">
                    <input type="checkbox" id="remember" name="remember" class="mr-2 rounded focus:ring-blue-500">
                    <span class="text-sm">Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                id="loginBtn"
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span class="iconify" data-icon="mdi:login"></span>
                Sign In
            </button>

            <div class="mt-4 text-center">
                <a href="/forgot-password.php" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                    Forgot password?
                </a>
            </div>
        </form>

    </div>

    <!-- Footer -->
    <p class="text-center text-blue-100 mt-6 text-sm">
        © 2025 Employee Management System. All rights reserved.
    </p>
</div>

<!-- Login Script -->
<script>
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const togglePasswordBtn = document.getElementById('togglePassword');
const rememberCheckbox = document.getElementById('remember');
const loginBtn = document.getElementById('loginBtn');
const alertBox = document.getElementById('alert');

// Pre-fill email if remembered
document.addEventListener('DOMContentLoaded', () => {
    const rememberedEmail = localStorage.getItem('remembered_email');
    if (rememberedEmail) {
        emailInput.value = rememberedEmail;
        // Checkbox remains unchecked and password remains hidden by default on reload
    }
});

// Toggle password visibility via button
togglePasswordBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    updatePasswordIcon(isPassword);
});

rememberCheckbox.addEventListener('change', () => {
    const isVisible = rememberCheckbox.checked;
    passwordInput.type = isVisible ? 'text' : 'password';
    updatePasswordIcon(isVisible);
});

function updatePasswordIcon(isVisible) {
    const icon = togglePasswordBtn.querySelector('.iconify');
    if (icon) {
        icon.setAttribute('data-icon', isVisible ? 'mdi:eye-off' : 'mdi:eye');
        if (window.Iconify) window.Iconify.build();
    }
}

// Show alert
function showAlert(message, type) {
    alertBox.textContent = message;
    alertBox.className = type === 'success' 
        ? 'bg-green-100 border border-green-400 text-green-700 p-4 rounded text-sm font-medium' 
        : 'bg-red-100 border border-red-400 text-red-700 p-4 rounded text-sm font-medium';
    alertBox.classList.remove('hidden');
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Validate email format
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Handle form submission
loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    alertBox.classList.add('hidden');

    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const remember = rememberCheckbox.checked;

    // Client-side validation
    if (!email) {
        showAlert('Please enter your email address.', 'error');
        emailInput.focus();
        return;
    }

    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address.', 'error');
        emailInput.focus();
        return;
    }

    if (!password) {
        showAlert('Please enter your password.', 'error');
        passwordInput.focus();
        return;
    }

    // Save/Clear remembered email
    if (remember) {
        localStorage.setItem('remembered_email', email);
    } else {
        localStorage.removeItem('remembered_email');
    }

    // Disable button during submission
    loginBtn.disabled = true;
    const originalHTML = loginBtn.innerHTML;
    loginBtn.innerHTML = '<span class="iconify animate-spin" data-icon="mdi:loading"></span> Signing in...';
    if (window.Iconify) window.Iconify.build();

    try {
        const response = await fetch('/login-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&remember=${remember ? 1 : 0}`
        });

        const data = await response.json();

        if (data.dpl || data.success) {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = '/index.php?page=dashboard';
            }, 1500);
        } else {
            showAlert(data.message || 'Login failed. Please try again.', 'error');
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalHTML;
            // passwordInput.value = ''; // Keep password for user convenience
            if (window.Iconify) window.Iconify.build();
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Connection error. Please try again.', 'error');
        loginBtn.disabled = false;
        loginBtn.innerHTML = originalHTML;
        if (window.Iconify) window.Iconify.build();
    }
});

// Clear alert on input
emailInput.addEventListener('input', () => alertBox.classList.add('hidden'));
passwordInput.addEventListener('input', () => alertBox.classList.add('hidden'));

// Reload iconify after script loads
if (window.Iconify) {
    window.Iconify.build();
}
</script>

</body>
</html>
