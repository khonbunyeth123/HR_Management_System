<?php

namespace App\Core;

class Application
{
    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // ✅ CRITICAL: Handle API routes BEFORE any auth checks or web routes
        if (strpos($uri, '/api/') === 0) {
            // Don't start session for API routes
            require __DIR__ . '/../../routes/api.php';
            exit; // Must exit here to prevent web.php from loading
        }

        // ✅ Handle web routes (these may have auth middleware)
        require __DIR__ . '/../../routes/web.php';
    }
}