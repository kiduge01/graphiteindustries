<?php
declare(strict_types=1);
if ($action === 'list') {
    $resource = $_GET['resource'] ?? '';
    if (!is_string($resource)) {
        fail('Invalid collection.');
    }
    output(collection($resource));
}
if ($action === 'mediaItem') {
    $url = $_GET['url'] ?? '';
    if (!is_string($url)) {
        fail('Invalid media URL.');
    }
    $item = query('SELECT * FROM media WHERE url=?', [$url])->fetch();
    output(['item' => $item ?: null]);
}
if ($action === 'export') {
    require_user(true);
    $tables = ['pages', 'posts', 'settings', 'media', 'enquiries', 'revisions'];
    $export = ['exported_at' => gmdate('c')];
    foreach ($tables as $table) {
        $export[$table] = all('SELECT * FROM ' . $table);
    }
    output($export);
}
if ($action === 'dashboard') {
    $settings = array_column(all('SELECT * FROM settings'), 'setting_value', 'setting_key');
    $since = gmdate('Y-m-d H:i:s', time() - 30 * 86400);
    $totals = query("SELECT COUNT(*) AS views, COUNT(DISTINCT sid) AS visitors FROM events WHERE kind='page' AND created_at>=?", [$since])->fetch();
    $lists = [];
    foreach (['posts', 'media', 'enquiries'] as $resource) {
        $lists[$resource] = collection($resource);
    }
    $lists['users'] = $user['role'] === 'admin' ? collection('users') : ['items' => [], 'page' => 1, 'pages' => 1, 'total' => 0];
    output([
        'pages' => all('SELECT * FROM pages'),
        'posts' => $lists['posts']['items'],
        'media' => $lists['media']['items'],
        'enquiries' => $lists['enquiries']['items'],
        'users' => $lists['users']['items'],
        'pagination' => $lists,
        'revisions' => all('SELECT * FROM revisions ORDER BY id DESC LIMIT 200'),
        'settings' => $settings,
        'stats' => [
            'visitors' => (int)$totals['visitors'],
            'pageviews' => (int)$totals['views'],
            'enquiries' => (int)query('SELECT COUNT(*) FROM enquiries')->fetchColumn(),
            'new_enquiries' => (int)query("SELECT COUNT(*) FROM enquiries WHERE status='new'")->fetchColumn(),
            'downloads' => (int)query("SELECT COUNT(*) FROM events WHERE kind='download' AND created_at>=?", [$since])->fetchColumn(),
            'daily' => all("SELECT SUBSTR(created_at,1,10) AS day,COUNT(*) AS views FROM events WHERE kind='page' AND created_at>=? GROUP BY SUBSTR(created_at,1,10) ORDER BY day", [$since]),
            'top' => all("SELECT page,COUNT(*) AS views FROM events WHERE kind='page' AND created_at>=? GROUP BY page ORDER BY views DESC LIMIT 10", [$since]),
            'sources' => all("SELECT source,COUNT(*) AS views FROM events WHERE kind='page' AND created_at>=? GROUP BY source ORDER BY views DESC LIMIT 10", [$since]),
        ],
    ]);
}
