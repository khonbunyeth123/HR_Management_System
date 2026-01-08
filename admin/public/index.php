<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ CRITICAL FIX: Check if this is an API request BEFORE doing anything else
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '/api/') === 0) {
    return;
}

// ✅ Now safe to start session for web requests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default session values if not set (for testing)
if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = true;  // Auto-login for testing
}

if (!isset($_SESSION['uname'])) {
    $_SESSION['uname'] = 'Admin User';
}

// Get the page parameter
$page = $_GET['page'] ?? 'dashboard';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;

// If not logged in and trying to access protected pages, redirect to login
$protectedPages = ['dashboard', 'employee', 'attendance', 'leave', 'audits', 'report', 'user'];

if (!$isLoggedIn && in_array($page, $protectedPages)) {
    header('Location: /login.php');
    exit;
}

// If logged in and trying to access login page, redirect to dashboard
if ($isLoggedIn && ($page === 'login' || $page === '')) {
    header('Location: /index.php?page=dashboard');
    exit;
}

// Include layout files
$baseDir = __DIR__;
$layoutDir = $baseDir . '/../resources/views/layouts';
$viewDir = $baseDir . '/../resources/views';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoorStep - Employee Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
</head>
<body class="bg-gray-100">

<?php if ($isLoggedIn): ?>
    <!-- Logged In Layout -->
    <div class="flex flex-col min-h-screen">
        <!-- Navbar (Full Width) -->
        <?php include $layoutDir . '/navbar.php'; ?>
        
        <div class="flex flex-1">
            <!-- Sidebar -->
            <?php 
            if (file_exists($layoutDir . '/sidebar.php')) {
                include $layoutDir . '/sidebar.php';
            }
            ?>
            
            <!-- Main Content -->
            <main class="flex-1 flex flex-col">
                <!-- Page Header -->
                <?php 
                if (file_exists($layoutDir . '/header.php')) {
                    include $layoutDir . '/header.php';
                }
                ?>
                
                <!-- Page Content -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <?php
                    // Load the appropriate page
                    $pageFile = $viewDir . '/' . $page . '.php';
                    
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo '<div class="text-center text-gray-500 mt-10"><h1>Page not found</h1></div>';
                    }
                    ?>
                </div>
            </main>
        </div>

        <!-- Footer -->
        <?php 
        if (file_exists($layoutDir . '/footer.php')) {
            include $layoutDir . '/footer.php';
        }
        ?>
    </div>

<?php else: ?>
    <!-- Not Logged In - Show Login Page -->
    <?php
    if ($page === 'login' || $page === '') {
        include $baseDir . '/login.php';
    } else {
        header('Location: /login.php');
        exit;
    }
    ?>

<?php endif; ?>

<script src="/assets/js/jquery.js"></script>
<script src="/assets/js/script.js"></script>

</body>
</html>