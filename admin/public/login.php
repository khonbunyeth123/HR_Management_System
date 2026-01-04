
<?php 

 require_once dirname(__DIR__) . '/../resources/views/layouts/header.php';
 
?>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account to continue</p>
        </div>

        <div id="alertBox"></div>

        <form id="loginForm" class="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                        autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required
                        autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePassword">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                Sign In
            </button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="register.php"
                    style="color: #667eea; text-decoration: none; font-weight: 600;">Sign Up</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            const emailInput = $('#email');
            const passwordInput = $('#password');
            const togglePasswordBtn = $('#togglePassword');
            const loginBtn = $('#loginBtn');
            const loginForm = $('#loginForm');
            const alertBox = $('#alertBox');

            // Toggle password visibility
            togglePasswordBtn.click(function (e) {
                e.preventDefault();
                const isPassword = passwordInput.attr('type') === 'password';
                passwordInput.attr('type', isPassword ? 'text' : 'password');
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            // Show alert message
            function showAlert(message, type = 'error') {
                alertBox.html(`
                    <div class="alert alert-${type}">
                        <i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i>
                        ${message}
                    </div>
                `);
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Validate email
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Handle form submission
            loginForm.submit(function (e) {
                e.preventDefault();
                alertBox.empty();

                const email = emailInput.val().trim();
                const password = passwordInput.val();

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

                if (password.length < 6) {
                    showAlert('Password must be at least 6 characters.', 'error');
                    passwordInput.focus();
                    return;
                }

                // Submit form via AJAX
                loginBtn.addClass('loading').prop('disabled', true);
                loginBtn.html('<span class="spinner"></span>Signing in...');

                $.ajax({
                    url: 'login-user.php',
                    type: 'POST',
                    data: {
                        email: email,
                        password: password,
                        remember: $('#remember').is(':checked') ? 1 : 0
                    },
                    dataType: 'json',
                    timeout: 5000,
                    success: function (response) {
                        if (response.dpl === true) {
                            setTimeout(() => {
                                window.location.href = 'index.php';
                            }, 1000);
                            showAlert('Login successful! Redirecting...', 'success');
                        } else {
                            const message = response.message || 'Login failed. Please try again.';
                            showAlert(message, 'error');
                            loginBtn.removeClass('loading').prop('disabled', false);
                            loginBtn.html('Sign In');
                            passwordInput.val('').focus();
                        }
                    },
                    error: function (xhr, status, error) {
                        showAlert('Connection error. Please try again.', 'error');
                        loginBtn.removeClass('loading').prop('disabled', false);
                        loginBtn.html('Sign In');
                    }
                });
            });

            // Clear alert when user starts typing
            emailInput.add(passwordInput).on('input', function () {
                alertBox.empty();
            });
        });
    </script>
</body>

</html>