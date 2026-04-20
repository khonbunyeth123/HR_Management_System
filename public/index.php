<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/PermissionHelper.php';

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
// If API request -> go to api routes
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

$pagePermissions = [
    'dashboard' => ['dashboard.view'],
    'attendance' => ['attendance.view'],
    'employee' => ['employee.view', 'employees.view'],
    'leave' => ['leave.view'],
    'report' => ['report.view'],
    'report/report_daily' => ['report.view_daily', 'report.view'],
    'report/report_summary' => ['report.view_summary', 'report.view'],
    'report/report_detail' => ['report.view_detail', 'report.view'],
    'report/report_top_employee' => ['report.view_top', 'report.view'],
    'user' => ['user.view', 'users.view'],
    'roles' => ['role.view', 'roles.view'],
    'permissions' => ['permission.view', 'permissions.view'],
    'audits' => ['audits.view', 'audit.view'],
];

$protectedPages = [
    'dashboard',
    'employee',
    'attendance',
    'leave',
    'audits',
    'report',
    'report/report_daily',
    'report/report_summary',
    'report/report_detail',
    'report/report_top_employee',
    'user',
    'roles',
    'permissions',
];

$canAccessSlugs = static function (array $slugs): bool {
    if (function_exists('hasAnyPermissionSlugs')) {
        return hasAnyPermissionSlugs($slugs);
    }

    foreach ($slugs as $slug) {
        if (is_string($slug) && hasPermissionSlug($slug)) {
            return true;
        }
    }

    return false;
};

$canAccessPage = static function (string $pageName) use ($pagePermissions, $canAccessSlugs): bool {
    if (!isset($pagePermissions[$pageName])) {
        return true;
    }

    $slugs = $pagePermissions[$pageName];
    $slugs = is_array($slugs) ? $slugs : [$slugs];

    return $canAccessSlugs($slugs);
};

$findFirstAccessiblePage = static function () use ($pagePermissions, $canAccessSlugs): ?string {
    foreach ($pagePermissions as $candidate => $permissionSlugs) {
        $slugs = is_array($permissionSlugs) ? $permissionSlugs : [$permissionSlugs];
        if ($canAccessSlugs($slugs)) {
            return $candidate;
        }
    }

    return null;
};

$isProtectedPage = in_array($page, $protectedPages, true) || isset($pagePermissions[$page]);

/* ================= AUTH CHECK ================= */
if (!$isLoggedIn && $isProtectedPage) {
    header('Location: /login.php');
    exit;
}

if ($isLoggedIn && $page === 'login') {
    $homePage = $findFirstAccessiblePage() ?? 'dashboard';
    header('Location: /index.php?page=' . urlencode($homePage));
    exit;
}

/* ================= PAGE-LEVEL RBAC CHECK ================= */
if ($isLoggedIn && $isProtectedPage && !$canAccessPage($page)) {
    $homePage = $findFirstAccessiblePage();

    if ($homePage !== null && $homePage !== $page) {
        header('Location: /index.php?page=' . urlencode($homePage));
        exit;
    }

    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <style>
            body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
            .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
            .card { background: #fff; border-radius: 12px; padding: 24px; max-width: 560px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
            h1 { margin: 0 0 12px; font-size: 24px; }
            p { margin: 0; line-height: 1.5; }
            a { display: inline-block; margin-top: 16px; text-decoration: none; color: #1d4ed8; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="card">
                <h1>403 - Access Denied</h1>
                <p>You do not have permission to access this page.</p>
                <a href="/index.php?page=dashboard">Go to dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* ================= VIEW SYSTEM ================= */
$baseDir = __DIR__;
$layoutDir = $baseDir . '/../resources/views/layouts';
$viewDir = $baseDir . '/../resources/views';

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

                <div style="flex: 1; overflow-y: auto; overflow-x: hidden;">
                    <?php
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

