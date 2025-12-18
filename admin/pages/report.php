<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Report</title>
    <link href="path-to-tailwind.css" rel="stylesheet">
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Monthly Report</h1>
    <p>Content of Monthly Report goes here.</p>
</div>
</body>
</html>
