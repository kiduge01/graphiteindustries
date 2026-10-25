<?php
declare(strict_types=1);
// Select with GRAPHITE_CONFIG_FILE=/absolute/path/app/config.production.php.
// Supply these variables to both PHP-FPM/Apache and scheduled CLI commands.
$required = static function (string $name): string {
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException('Required production variable is missing: ' . $name);
    }
    return $value;
};
$url = rtrim($required('GRAPHITE_BASE_URL'), '/');
$parts = parse_url($url);
if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
    || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
    || in_array(strtolower($parts['host']), ['localhost', '127.0.0.1', '::1'], true)) {
    throw new RuntimeException('GRAPHITE_BASE_URL must be the public HTTPS URL.');
}
$key = $required('GRAPHITE_RATE_LIMIT_KEY');
if (!preg_match('/\A[a-f0-9]{64}\z/i', $key)) {
    throw new RuntimeException('GRAPHITE_RATE_LIMIT_KEY must contain 64 random hexadecimal characters.');
}
$dsn = $required('GRAPHITE_DB_DSN');
$user = $required('GRAPHITE_DB_USER');
if (!str_starts_with($dsn, 'mysql:') || !preg_match('/(?:^|;)dbname=[^;]+/', substr($dsn, 6)) || strtolower($user) === 'root') {
    throw new RuntimeException('Use a MySQL DSN with a database name and a dedicated database user.');
}
return [
    'dsn' => $dsn,
    'db_user' => $user,
    'db_password' => $required('GRAPHITE_DB_PASSWORD'),
    'base_url' => $url,
    'session_name' => 'graphite_admin',
    'secure_cookies' => true,
    'rate_limit_key' => $key,
];
