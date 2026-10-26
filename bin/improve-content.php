<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';
$baseline = json_decode(file_get_contents(ROOT . '/app/content-baseline-20261025.json'), true, 512, JSON_THROW_ON_ERROR);
$reviewed = json_decode(file_get_contents(ROOT . '/app/seed.json'), true, 512, JSON_THROW_ON_ERROR);
$before = array_column($baseline['pages'], null, 'id');
$apply = in_array('--apply', $argv, true);
$updates = [];
foreach ($reviewed['pages'] as $new) {
    $old = query('SELECT * FROM pages WHERE id=?', [$new['id']])->fetch();
    if (!$old || !isset($before[$new['id']])) { echo "SKIP {$new['id']}: no existing baseline page.\n"; continue; }
    $fields = [];
    foreach (['title', 'eyebrow', 'summary', 'body', 'image', 'seo'] as $field) {
        if ($new[$field] === $before[$new['id']][$field] || $new[$field] === $old[$field]) continue;
        if ($old[$field] !== $before[$new['id']][$field]) {
            echo "CONFLICT {$new['id']}.$field: custom content preserved.\n";
            continue;
        }
        $fields[$field] = $new[$field];
    }
    if ($fields) { $updates[] = [$old, $fields]; echo ($apply ? 'UPDATE ' : 'READY ') . $new['id'] . ': ' . implode(', ', array_keys($fields)) . "\n"; }
}
if (!$apply) { echo "Review only. Add --apply to save eligible changes with revision history.\n"; exit; }
db()->beginTransaction();
try {
    foreach ($updates as [$old, $fields]) {
        $current = query('SELECT * FROM pages WHERE id=?' . (db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : ''), [$old['id']])->fetch();
        if ($current !== $old) throw new RuntimeException('Content changed during update; no changes applied.');
        query('INSERT INTO revisions (page_id,content,created_at) VALUES (?,?,?)', [$old['id'], json_encode($old, JSON_THROW_ON_ERROR), gmdate('Y-m-d H:i:s')]);
        $sql = implode(',', array_map(fn($f) => "$f=?", array_keys($fields)));
        query("UPDATE pages SET $sql,updated_at=? WHERE id=?", [...array_values($fields), gmdate('Y-m-d H:i:s'), $old['id']]);
    }
    db()->commit();
    echo count($updates) . " pages updated; previous content saved in revision history.\n";
} catch (Throwable $e) { db()->rollBack(); throw $e; }
