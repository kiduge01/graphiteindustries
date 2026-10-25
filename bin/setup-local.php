<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$root = dirname(__DIR__);
if (is_file($root . '/app/config.php')) {
    throw new RuntimeException('Configuration already exists. No changes made.');
}
$admin = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', getenv('GRAPHITE_SETUP_DB_USER') ?: 'root', getenv('GRAPHITE_SETUP_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$exists = $admin->query("SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='graphite'")->fetchColumn();
if ($exists) {
    throw new RuntimeException('The graphite database already exists. Configure it manually to avoid overwriting data.');
}
$password = bin2hex(random_bytes(32));
$admin->exec('CREATE DATABASE graphite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$admin->exec("CREATE USER 'graphite_app'@'127.0.0.1' IDENTIFIED BY " . $admin->quote($password));
$admin->exec("GRANT ALL PRIVILEGES ON graphite.* TO 'graphite_app'@'127.0.0.1'");
$config = [
    'dsn' => 'mysql:host=127.0.0.1;dbname=graphite;charset=utf8mb4',
    'db_user' => 'graphite_app',
    'db_password' => $password,
    'base_url' => 'http://localhost/graphite/public',
    'session_name' => 'graphite_admin',
    'secure_cookies' => false,
    'rate_limit_key' => bin2hex(random_bytes(32)),
];
file_put_contents($root . '/app/config.php', "<?php\nreturn " . var_export($config, true) . ";\n");
require __DIR__ . '/install.php';
$initialPassword = bin2hex(random_bytes(16));
query('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,?)', ['Administrator', 'admin@graphite.com', password_hash($initialPassword, PASSWORD_DEFAULT), 'admin', gmdate('Y-m-d H:i:s')]);
if (!is_dir($root . '/storage')) {
    mkdir($root . '/storage', 0700, true);
}
file_put_contents($root . '/storage/local-admin.txt', "Local CMS: http://localhost/graphite/public/admin.php\nEmail: admin@graphite.com\nPassword: " . $initialPassword . "\nChange this password after signing in, then delete this file.\n");
chmod($root . '/storage/local-admin.txt', 0600);
echo "Local installation complete. Initial credentials saved privately in storage/local-admin.txt.\n";
