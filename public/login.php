<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.2.0/iconify.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-600 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Login Card -->
    <div class="bg-white rounded-lg shadow-xl p-8">
        <div class="text-center mb-8">
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
                    <span class="iconify inline mr-2" data-icon="mdi:email"></span>
                    Email Address
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="you@example.com"
                    autocomplete="email"
                >
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-semibold mb-2">
                    <span class="iconify inline mr-2" data-icon="mdi:lock"></span>
                    Password
                </label>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <button
                        type="button"
                        id="togglePassword"
                        class="absolute right-3 top-2 text-gray-500 hover:text-gray-700"
                    >
                        <span class="iconify" data-icon="mdi:eye"></span>
                    </button>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center text-gray-700 cursor-pointer">
                    <input type="checkbox" id="remember" name="remember" class="mr-2 rounded focus:ring-blue-500">
                    <span class="text-sm">Remember me</span>
                </label>
                <a href="/forgot-password.php" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                id="loginBtn"
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span class="iconify" data-icon="mdi:login"></span>
                Log In
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Don't have an account?</span>
            </div>
        </div>

        <!-- Sign Up Link -->
        <a href="/register.php" class="block w-full text-center text-blue-600 font-semibold hover:text-blue-700 hover:underline">
            Create Account
        </a>
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

// Toggle password visibility via "Remember me" checkbox
// User reported: "when click Remember me checkbox I'm nor see password"
// Fix: Allow the checkbox to also reveal the password for convenience.
rememberCheckbox.addEventListener('change', () => {
    passwordInput.type = rememberCheckbox.checked ? 'text' : 'password';
    updatePasswordIcon(rememberCheckbox.checked);
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