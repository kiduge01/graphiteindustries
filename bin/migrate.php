<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../app/bootstrap.php';
$mysql = db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
$columns = $mysql ? array_column(all('SHOW COLUMNS FROM users'), 'Field') : array_column(all('PRAGMA table_info(users)'), 'name');
if (!in_array('active', $columns, true)) {
    query('ALTER TABLE users ADD COLUMN active INTEGER NOT NULL DEFAULT 1');
}
foreach (['enquiries_status_date' => ['enquiries', 'status,created_at'], 'posts_status_date' => ['posts', 'status,published_at']] as $name => [$table, $fields]) {
    $exists = $mysql
    ? query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND index_name=? AND table_name=?', [$name, $table])->fetchColumn()
    : query("SELECT COUNT(*) FROM sqlite_master WHERE type='index' AND name=?", [$name])->fetchColumn();
    if (!$exists) {
        query("CREATE INDEX $name ON $table ($fields)");
    }
}
echo "Schema is current.\n";
