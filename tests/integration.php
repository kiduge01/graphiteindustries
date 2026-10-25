<?php
declare(strict_types=1);
// Native PHP integration tests: isolated database, uploads and HTTP server.
$root = dirname(__DIR__);
$directory = $root . '/storage/test-' . bin2hex(random_bytes(6));
mkdir($directory, 0700, true);
mkdir($directory . '/uploads');
$mysql = in_array('--mysql', $argv, true);
$databaseName = 'graphite_test_' . bin2hex(random_bytes(6));
$databaseAdmin = null;
$server = null;
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
function command(array $arguments, array $environment): string
{
    $pipes = [];
    $process = proc_open(array_merge([PHP_BINARY], $arguments), [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__), $environment);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0) {
        throw new RuntimeException('Command failed: ' . implode(' ', $arguments) . "\n" . $output . $errors);
    }
    return $output;
}
try {
    if ($mysql) {
        $databaseAdmin = new PDO(getenv('GRAPHITE_TEST_MYSQL_DSN') ?: 'mysql:host=127.0.0.1;charset=utf8mb4', getenv('GRAPHITE_TEST_MYSQL_USER') ?: 'root', getenv('GRAPHITE_TEST_MYSQL_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $databaseAdmin->exec('CREATE DATABASE ' . $databaseName . ' CHARACTER SET utf8mb4');
    }
    $config = [
        'dsn' => $mysql ? (getenv('GRAPHITE_TEST_MYSQL_DSN') ?: 'mysql:host=127.0.0.1;charset=utf8mb4') . ';dbname=' . $databaseName : 'sqlite:' . $directory . '/database.sqlite',
        'db_user' => $mysql ? (getenv('GRAPHITE_TEST_MYSQL_USER') ?: 'root') : '',
        'db_password' => $mysql ? (getenv('GRAPHITE_TEST_MYSQL_PASSWORD') ?: '') : '',
        'secure_cookies' => false,
        'session_name' => 'graphite_test_' . bin2hex(random_bytes(4)),
        'rate_limit_key' => bin2hex(random_bytes(32)),
        'base_url' => 'http://example.test/graphite/public',
        'uploads_directory' => $directory . '/uploads',
        'backup_directory' => $directory . '/backups',
        'session_directory' => $directory . '/sessions',
    ];
    $configFile = $directory . '/config.php';
    file_put_contents($configFile, '<?php return ' . var_export($config, true) . ';');
    $environment = array_merge(getenv(), ['GRAPHITE_CONFIG_FILE' => $configFile, 'XDEBUG_MODE' => 'off']);
    command(['bin/install.php'], $environment);
    command(['bin/migrate.php'], $environment);
    $pdo = new PDO($config['dsn'], $config['db_user'], $config['db_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $insert = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,?)');
    $insert->execute(['Test Admin', 'admin@example.test', password_hash('fixture-admin-password', PASSWORD_DEFAULT), 'admin', gmdate('Y-m-d H:i:s')]);
    $adminId = (int)$pdo->lastInsertId();
    $insert->execute(['Test Editor', 'editor@example.test', password_hash('fixture-editor-password', PASSWORD_DEFAULT), 'editor', gmdate('Y-m-d H:i:s')]);
    $editorId = (int)$pdo->lastInsertId();
    $socket = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int)substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
    fclose($socket);
    $url = 'http://127.0.0.1:' . $port;
    $server = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $root . '/public'], [0 => ['pipe', 'r'], 1 => ['file', $directory . '/server.log', 'a'], 2 => ['file', $directory . '/server.log', 'a']], $serverPipes, $root, $environment);
    fclose($serverPipes[0]);
    $cookie = $directory . '/cookies.txt';
    $csrf = '';
    $request = function (string $path, ?array $data = null, int $expected = 200, ?string $token = null, ?array $multipart = null) use ($url, $cookie, &$csrf): array {
        $curl = curl_init($url . $path);
        $headers = ['X-CSRF-Token: ' . ($token ?? $csrf)];
        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_COOKIEJAR => $cookie, CURLOPT_COOKIEFILE => $cookie, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 10]);
        if ($data !== null || $multipart !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $multipart ?? json_encode($data));
        }
        $raw = curl_exec($curl);
        if ($raw === false) {
            throw new RuntimeException(curl_error($curl));
        }
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $head = substr($raw, 0, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        $body = substr($raw, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        curl_close($curl);
        check($status === $expected, "$path: expected $expected, got $status: $body");
        $json = json_decode($body, true);
        if (isset($json['csrf'])) {
            $csrf = $json['csrf'];
        }
        return ['body' => $body, 'headers' => $head, 'json' => $json];
    }
    ;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $connection = @fsockopen('127.0.0.1', $port);
        if ($connection) {
            fclose($connection);
            break;
        }
        usleep(100000);
    }
    $api = fn(string $action, ?array $data = null, int $expected = 200, ?string $token = null) => $request('/api.php?action=' . $action, $data, $expected, $token)['json'];
    $api('health');
    $api('dashboard', null, 401);
    $api('login', ['email' => 'admin@example.test', 'password' => 'fixture-admin-password'], 403, 'invalid');
    $api('login', ['email' => 'admin@example.test', 'password' => 'wrong'], 401);
    $api('login', ['email' => 'admin@example.test', 'password' => 'fixture-admin-password']);
    $public = $api('public');
    check(count($public['pages']) === 7, 'Seeded published pages');
    $page = array_values(array_filter($public['pages'], fn($p) => $p['id'] === 'about'))[0];
    $api('savePage', array_merge($page, ['status' => 'draft']));
    $request('/index.php?page=about', null, 404);
    $dashboard = $api('dashboard');
    $api('restore', ['id' => $dashboard['revisions'][0]['id']]);
    $literal = '$1 and \\value <safe>';
    $api('savePage', array_merge($page, ['seo' => $literal, 'summary' => $literal]));
    $rendered = $request('/index.php?page=about')['body'];
    check(str_contains($rendered, '<title>' . htmlspecialchars($literal, ENT_QUOTES, 'UTF-8') . '</title>'), 'Literal replacement references preserved in title');
    check(str_contains($rendered, 'content="' . htmlspecialchars($literal, ENT_QUOTES, 'UTF-8') . '"'), 'Literal summary preserved');
    $request('/index.php?page=missing', null, 404);
    $request('/index.php?page[]=home', null, 404);
    foreach (['privacy', 'terms', 'news'] as $route) {
        $body = $request('/index.php?page=' . $route)['body'];
        check(str_contains($body, '<article') && str_contains($body, '<link rel="canonical"'), 'Server rendering for ' . $route);
    }
    $request('/index.php?page=news&post=999999', null, 404);
    $api('savePost', ['title' => 'Test announcement', 'summary' => 'A summary', 'body' => 'Published test body', 'image' => '', 'status' => 'published']);
    $postId = (int)$pdo->query('SELECT MAX(id) FROM posts')->fetchColumn();
    check(str_contains($request('/index.php?page=news&post=' . $postId)['body'], 'Published test body'), 'Server rendered news article');
    $insertPost = $pdo->prepare('INSERT INTO posts (title,summary,body,image,status,published_at,updated_at) VALUES (?,?,?,?,?,?,?)');
    for ($i = 0; $i < 24; $i++) {
        $insertPost->execute(['Pagination article ' . $i, 'summary', 'body', '', 'published', gmdate('Y-m-d H:i:s'), gmdate('Y-m-d H:i:s')]);
    }
    $first = $api('list&resource=posts&page=1');
    $second = $api('list&resource=posts&page=2');
    check(count($first['items']) === 20 && count($second['items']) === 5 && $first['total'] === 25, 'Server pagination counts');
    check(!array_intersect(array_column($first['items'], 'id'), array_column($second['items'], 'id')), 'Pagination has no duplicates');
    check(count($api('public&newsPage=2')['posts']) === 5, 'Public news pagination');
    $request('/index.php?page=news&newsPage=3', null, 404);
    $api('list&resource=posts&page=-1', null, 400);
    $api('list&resource=posts&search=announcement');
    $api('enquire', ['name' => 'Test Person', 'email' => 'bad', 'category' => 'General enquiry', 'message' => 'Example test message'], 400);
    $enquiry = ['name' => 'Test Person', 'email' => 'person@example.test', 'organisation' => 'Test', 'category' => 'General enquiry', 'message' => 'Example test message'];
    $api('enquire', $enquiry);
    $enquiries = $api('list&resource=enquiries&search=Test%20Person&status=new');
    check($enquiries['total'] === 1, 'Server enquiry search and filter');
    $api('enquiryStatus', ['id' => $enquiries['items'][0]['id'], 'status' => 'resolved']);
    $api('track', ['kind' => 'page', 'page' => 'home', 'sid' => 'test-session-123456789', 'referrer' => 'https://example.test/path?private=secret']);
    $dashboard = $api('dashboard');
    check($dashboard['stats']['visitors'] === 1 && $dashboard['stats']['sources'][0]['source'] === 'example.test', 'Analytics counts and sanitised referrer');
    $request('/api.php?action=upload', null, 200, null, ['file' => new CURLFile($root . '/public/assets/logo.png', 'image/png', 'logo.png')]);
    file_put_contents($directory . '/bad.php', '<?php echo 1;');
    $request('/api.php?action=upload', null, 400, null, ['file' => new CURLFile($directory . '/bad.php', 'application/x-httpd-php', 'bad.php')]);
    $api('savePage', array_merge($page, ['image' => 'uploads/../../api.php']), 400);
    $api('updateUser', ['id' => $adminId, 'role' => 'editor', 'active' => false], 400);
    $api('updateUser', ['id' => $editorId, 'role' => 'editor', 'active' => false]);
    $api('logout', []);
    $api('health');
    $api('login', ['email' => 'editor@example.test', 'password' => 'fixture-editor-password'], 401);
    $api('login', ['email' => 'admin@example.test', 'password' => 'fixture-admin-password']);
    $api('updateUser', ['id' => $editorId, 'role' => 'editor', 'active' => true]);
    $export = $api('export');
    check(count($export['posts']) === 25 && !isset($export['users']), 'Complete export independent of dashboard pagination');
    $api('logout', []);
    $api('health');
    $api('login', ['email' => 'editor@example.test', 'password' => 'fixture-editor-password']);
    $api('settings', $public['settings'], 403);
    $api('updateUser', ['id' => $adminId, 'role' => 'editor', 'active' => false], 403);
    $api('list&resource=users', null, 403);
    $api('export', null, 403);
    $sitemap = $request('/sitemap.php')['body'];
    check(str_contains($sitemap, 'page=privacy') && str_contains($sitemap, 'page=news&amp;post='), 'Sitemap includes legal pages and articles');
    check(str_contains($request('/robots.php')['body'], 'Disallow: /graphite/public/admin.php'), 'Subdirectory crawler rules');
    command(['bin/backup.php'], $environment);
    $backup = glob($directory . '/backups/*.zip')[0];
    check(str_contains(command(['bin/restore.php', $backup], $environment), 'No data changed'), 'Restore dry-run');
    $pdo->exec('DELETE FROM posts');
    command(['bin/restore.php', $backup, '--apply'], $environment);
    check((int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn() === 25, 'Full database restoration');
    check(count(glob($directory . '/uploads/*.png')) === 1, 'Media restoration');
    $api('dashboard', null, 401);
    $badZip = new ZipArchive();
    $badZip->open($directory . '/unsafe.zip', ZipArchive::CREATE);
    $badZip->addFromString('../escape.php', '<?php echo 1;');
    $badZip->close();
    try {
        command(['bin/restore.php', $directory . '/unsafe.zip'], $environment);
        throw new LogicException('Unsafe archive accepted');
    }
    catch (RuntimeException $e) {
        check(str_contains($e->getMessage(), 'unexpected path'), 'Unsafe archive paths rejected');
    }
    echo 'PASS: ' . $checks . ' checks on ' . ($mysql ? 'MySQL' : 'SQLite') . ".\n";
}
finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    $insert = $insertPost = null;
    $pdo = null;
    if ($databaseAdmin) {
        $databaseAdmin->exec('DROP DATABASE ' . $databaseName);
    }
    $resolved = realpath($directory);
    if ($resolved && str_starts_with($resolved, realpath($root . '/storage') . DIRECTORY_SEPARATOR . 'test-')) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resolved, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($resolved);
    }
}
