<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= FIX BASE PATH ================= */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// IMPORTANT: change this if your folder name changes
$basePath = '/project_doorstep/my_project_3/admin';

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

if ($uri === '') {
    $uri = '/';
}

/* ================= API ROUTING ================= */
// If API request → go to api routes
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/../routes/api.php';
    exit;
}

/* ================= WEB (NORMAL SITE) ================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;

$page = $_GET['page'] ?? 'dashboard';

$protectedPages = ['dashboard', 'employee', 'attendance', 'leave', 'audits', 'report', 'user'];

/* ================= AUTH CHECK ================= */
if (!$isLoggedIn && in_array($page, $protectedPages)) {
    header('Location: /login.php');
    exit;
}

if ($isLoggedIn && $page === 'login') {
    header('Location: /index.php?page=dashboard');
    exit;
}

/* ================= VIEW SYSTEM ================= */
$baseDir = __DIR__;
$layoutDir = $baseDir . '/../resources/views/layouts';
$viewDir = $baseDir . '/../resources/views';
$pageFile = $viewDir . '/' . $page . '.php';
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if ($isAjax) {
    if (!$isLoggedIn && in_array($page, $protectedPages)) {
        http_response_code(401);
        exit;
    }
    if (file_exists($pageFile)) {
        include $pageFile;
    } else {
        echo '<div class="text-center text-gray-500 mt-10"><h1>Page not found</h1></div>';
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoorStep Technology Co.,Ltd</title>

    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
</head>
<body class="bg-gray-100 overflow-x-hidden" style="margin: 0; padding: 0;">

<?php if ($isLoggedIn): ?>
    <div class="flex flex-col" style="height: 100vh;">
        <?php include $layoutDir . '/navbar.php'; ?>

        <div class="flex flex-1" style="overflow: hidden;">
            <?php
            if (file_exists($layoutDir . '/sidebar.php')) {
                include $layoutDir . '/sidebar.php';
            }
            ?>

            <main class="flex-1 flex flex-col" style="overflow: hidden;">
                <?php
                if (file_exists($layoutDir . '/header.php')) {
                    include $layoutDir . '/header.php';
                }
                ?>

                <div style="flex: 1; overflow-y: auto; overflow-x: hidden;" id="content">
                    <?php
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo '<div class="text-center text-gray-500 mt-10"><h1>Page not found</h1></div>';
                    }
                    ?>
                </div>
            </main>
        </div>

        <?php
        if (file_exists($layoutDir . '/footer.php')) {
            include $layoutDir . '/footer.php';
        }
        ?>
    </div>

<?php else: ?>
    <?php include $baseDir . '/login.php'; ?>
<?php endif; ?>

<script src="/assets/js/jquery.js"></script>
<script src="/assets/js/script.js"></script>

</body>
</html>
