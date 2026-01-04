<?php
session_start();
require_once dirname(__DIR__) . '/database/cn.php';

// AUTH check here...
// ...

$page = $_GET['page'] ?? 'dashboard';

// VIEW PATHS
$viewBasePath = __DIR__ . "/../resources/views/";
$pagePath = $viewBasePath . $page . '.php';

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if ($isAjax) {
    if (file_exists($pagePath)) {
        require $pagePath;
    } else {
        http_response_code(404);
        echo "Page not found";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require $viewBasePath . 'layouts/header.php'; ?>
<body class="bg-gray-100 text-gray-900">
<?php require $viewBasePath . 'layouts/navbar.php'; ?>

<div class="flex min-h-[calc(100vh-56px)]">
    <?php require $viewBasePath . 'layouts/sidebar.php'; ?>

    <main class="flex-1 p-4" id="content">
        <?php
        if (file_exists($pagePath)) {
            require $pagePath;
        } else {
            echo "<h1 class='text-red-500'>404 - Page not found</h1>";
        }
        ?>
    </main>
</div>

<?php /* require $viewBasePath . 'layouts/footer.php'; */ ?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/3/3.2.0/iconify.min.js"></script>
<script src="/assets/js/script.js"></script>
</body>
</html>
