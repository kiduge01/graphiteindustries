<?php
declare(strict_types=1);
const ROOT = __DIR__ . '/..';
$configFile = getenv('GRAPHITE_CONFIG_FILE') ?: __DIR__ . '/config.php';
if (!is_file($configFile)) {
    throw new RuntimeException('Application configuration is missing.');
}
$config = require $configFile;
foreach (['database', 'validation', 'security', 'pagination'] as $module) {
    require_once __DIR__ . '/' . $module . '.php';
}
