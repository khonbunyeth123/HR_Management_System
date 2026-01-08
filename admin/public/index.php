<?php

// ✅ CRITICAL FIX: Check if this is an API request BEFORE doing anything else
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '/api/') === 0) {
    // This is an API request - don't start session or do auth checks
    // Just return and let Application.php handle it
    return;
}

// ✅ Now safe to start session for web requests
session_start();

// Get the page parameter
$page = $_GET['page'] ?? 'login';

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
    <title>Employee Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-gray-100">

<?php if ($isLoggedIn): ?>
    <!-- Logged In Layout -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include $layoutDir . '/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1">
            <!-- Header/Navbar -->
            <?php include $layoutDir . '/header.php'; ?>
            
            <!-- Page Content -->
            <div class="p-6">
                <?php
                // Load the appropriate page
                $pageFile = $viewDir . '/' . $page . '.php';
                
                if (file_exists($pageFile)) {
                    include $pageFile;
                } else {
                    include $viewDir . '/404.html';
                }
                ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include $layoutDir . '/footer.php'; ?>

<?php else: ?>
    <!-- Not Logged In - Show Login Page -->
    <?php
    if ($page === 'login' || $page === '') {
        include $baseDir . '/login.php';
    } else {
        // Redirect to login if trying to access protected page
        header('Location: /login.php');
        exit;
    }
    ?>

<?php endif; ?>

<script src="/assets/js/jquery.js"></script>
<script src="/assets/js/script.js"></script>

</body>
</html>