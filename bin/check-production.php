<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$failures = 0;
$check = static function (bool $ok, string $label) use (&$failures): void {
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) $failures++;
};
$check(PHP_VERSION_ID >= 80200, 'PHP 8.2 or newer');
foreach (['pdo_mysql', 'fileinfo', 'session', 'zip'] as $extension) {
    $check(extension_loaded($extension), 'Extension: ' . $extension);
}
$check(!filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN), 'PHP display_errors is disabled');
$check((bool)filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN), 'PHP error logging is enabled');
try {
    require dirname(__DIR__) . '/app/bootstrap.php';
    $url = parse_url($config['base_url'] ?? '');
    $check(($url['scheme'] ?? '') === 'https' && !empty($url['host']), 'Public HTTPS base URL');
    $check(($config['secure_cookies'] ?? false) === true, 'Secure session cookies');
    $check(preg_match('/\A[a-f0-9]{64}\z/i', $config['rate_limit_key'] ?? '') === 1, '64-character rate-limit key');
    $check(strtolower($config['db_user'] ?? '') !== 'root' && !empty($config['db_password']), 'Dedicated database credentials');
    $check(db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql', 'MySQL connection');
    $check((int)query("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1")->fetchColumn() > 0, 'Active administrator exists');
    foreach (['uploads' => $config['uploads_directory'] ?? ROOT . '/public/uploads', 'sessions' => $config['session_directory'] ?? ROOT . '/storage/sessions', 'backups' => $config['backup_directory'] ?? ROOT . '/storage/backups'] as $label => $directory) {
        $check(is_dir($directory) && is_writable($directory), 'Writable ' . $label . ' directory');
    }
} catch (Throwable $exception) {
    // Connection exceptions may include infrastructure details; never print them.
    $check(false, 'Configuration/database checks could not complete; verify private configuration and schema');
}
$check(!is_file(dirname(__DIR__) . '/storage/local-admin.txt'), 'Local administrator credential file absent');
echo 'This CLI check does not verify web-server rules, TLS, DNS, cron, or content approval.' . PHP_EOL;
exit($failures ? 1 : 0);
