<?php

namespace App\Core;

class Application
{
    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        require __DIR__ . '/../../routes/api.php';
        require __DIR__ . '/../../routes/web.php';
    }
}
