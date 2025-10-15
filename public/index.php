<?php

// When using PHP's built-in server, serve static files directly
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false; // Serve the requested resource as-is
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../routes/web.php';