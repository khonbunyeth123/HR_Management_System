<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/img/logo.png">
    <title>Forgot Password - Employee Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.2.0/iconify.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-600 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Forgot Password Card -->
    <div class="bg-white rounded-lg shadow-xl p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Forgot Password</h2>
            <p class="text-gray-600 text-sm mt-2">Enter your email address and we'll send you instructions to reset your password.</p>
        </div>

        <!-- Alert Messages -->
        <div id="alert" class="hidden mb-4 p-4 rounded text-sm font-medium"></div>

        <!-- Forgot Password Form -->
        <form id="forgotPasswordForm">
            <!-- Email Field -->
            <div class="mb-6">
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

            <!-- Submit Button -->
            <button
                type="submit"
                id="submitBtn"
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span class="iconify" data-icon="mdi:send"></span>
                Send Reset Link
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="/login.php" class="text-sm text-blue-600 hover:underline flex items-center justify-center gap-1">
                <span class="iconify" data-icon="mdi:arrow-left"></span>
                Back to Sign In
            </a>
        </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-blue-100 mt-6 text-sm">
        © 2025 Employee Management System. All rights reserved.
    </p>
</div>

<!-- Script -->
<script>
const forgotPasswordForm = document.getElementById('forgotPasswordForm');
const emailInput = document.getElementById('email');
const submitBtn = document.getElementById('submitBtn');
const alertBox = document.getElementById('alert');

// Show alert
function showAlert(message, type) {
    alertBox.textContent = message;
    alertBox.className = type === 'success' 
        ? 'bg-green-100 border border-green-400 text-green-700 p-4 rounded text-sm font-medium' 
        : 'bg-red-100 border border-red-400 text-red-700 p-4 rounded text-sm font-medium';
    alertBox.classList.remove('hidden');
}

// Validate email format
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Handle form submission
forgotPasswordForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    alertBox.classList.add('hidden');

    const email = emailInput.value.trim();

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

    // Disable button during submission
    submitBtn.disabled = true;
    const originalHTML = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="iconify animate-spin" data-icon="mdi:loading"></span> Sending...';
    if (window.Iconify) window.Iconify.build();

    try {
        const response = await fetch('/forgot-password-process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}`
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message || 'Instructions have been sent to your email.', 'success');
            forgotPasswordForm.reset();
            // Optional: hide button after success
            submitBtn.style.display = 'none';
        } else {
            showAlert(data.message || 'An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            if (window.Iconify) window.Iconify.build();
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Connection error. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
        if (window.Iconify) window.Iconify.build();
    }
});

// Clear alert on input
emailInput.addEventListener('input', () => alertBox.classList.add('hidden'));

// Reload iconify after script loads
if (window.Iconify) {
    window.Iconify.build();
}
</script>

</body>
</html>
