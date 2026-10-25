<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
try {
    require __DIR__ . '/../app/bootstrap.php';
    require ROOT . '/app/public-content.php';
    $path = rtrim(parse_url($config['base_url'], PHP_URL_PATH) ?: '', '/');
    echo "User-agent: *\n";
    foreach (['admin.php', 'admin.html', 'api.php'] as $file) {
        echo 'Disallow: ' . $path . '/' . $file . "\n";
    }
    echo 'Sitemap: ' . rtrim($config['base_url'], '/') . "/sitemap.php\n";
}
catch (Throwable $e) {
    http_response_code(503);
    echo "User-agent: *\nDisallow: /\n";
}
