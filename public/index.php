<?php

// Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdn.datatables.net; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;");

// When using PHP's built-in server, serve static files directly
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false; // Serve the requested resource as-is
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../routes/web.php';