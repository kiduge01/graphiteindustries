<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../app/bootstrap.php';
$path = $argv[1] ?? '';
$apply = in_array('--apply', $argv, true);
if (!is_file($path)) {
    fwrite(STDERR, "Usage: php bin/restore.php /absolute/path/backup.zip [--apply]\nWithout --apply, validates the archive without changing data.\n");
    exit(1);
}
$zip = new ZipArchive();
if ($zip->open($path) !== true) {
    throw new RuntimeException('Cannot open archive.');
}
$totalSize = 0;
for ($i = 0; $i < $zip->numFiles; $i++) {
    $entry = $zip->statIndex($i);
    $totalSize += $entry['size'];
    if ($totalSize > 256 * 1024 * 1024 || $entry['size'] > 128 * 1024 * 1024) {
        throw new RuntimeException('Archive exceeds restoration size limit.');
    }
    if ($entry['name'] !== 'database.json' && $entry['name'] !== 'uploads/.htaccess' &&
    !preg_match('~^uploads/[a-f0-9]{32}\.(jpg|png|webp|pdf)$~D', $entry['name'])) {
        throw new RuntimeException('Archive contains an unexpected path.');
    }
}
$data = json_decode($zip->getFromName('database.json') ?: '', true, 512, JSON_THROW_ON_ERROR);
$tables = ['users', 'pages', 'posts', 'settings', 'media', 'enquiries', 'revisions', 'events', 'rate_limits'];
if (($data['schema_version'] ?? null) !== 2 || array_diff($tables, array_keys($data['tables'] ?? [])) || array_diff(array_keys($data['tables'] ?? []), $tables)) {
    throw new RuntimeException('Unsupported or incomplete backup. Use a schema version 2 full backup.');
}
$schema = [];
foreach ($tables as $table) {
    $columns = db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
    ? array_column(all('SHOW COLUMNS FROM ' . $table), 'Field')
    : array_column(all('PRAGMA table_info(' . $table . ')'), 'name');
    $schema[$table] = $columns;
    if (!is_array($data['tables'][$table])) {
        throw new RuntimeException('Invalid backup table.');
    }
    foreach ($data['tables'][$table] as $row) {
        if (!is_array($row) || array_diff($columns, array_keys($row)) || array_diff(array_keys($row), $columns)) {
            throw new RuntimeException('Backup columns do not match the installed schema.');
        }
        foreach ($row as $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new RuntimeException('Invalid database value.');
            }
        }
    }
}
$admins = array_filter($data['tables']['users'], fn($u) => $u['role'] === 'admin' && (int)$u['active'] === 1);
if (!$admins) {
    throw new RuntimeException('Backup must contain an active administrator.');
}
$uploads = $config['uploads_directory'] ?? ROOT . '/public/uploads';
$backupDirectory = $config['backup_directory'] ?? ROOT . '/storage/backups';
if (!is_dir($backupDirectory)) {
    mkdir($backupDirectory, 0700, true);
}
$stage = $backupDirectory . '/restore-' . bin2hex(random_bytes(8));
mkdir($stage, 0700);
$staged = [];
$originals = [];
try {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (!str_starts_with($name, 'uploads/') || $name === 'uploads/.htaccess') {
            continue;
        }
        $bytes = $zip->getFromIndex($i);
        if ($bytes === false || strlen($bytes) > 8 * 1024 * 1024) {
            throw new RuntimeException('Invalid uploaded file size.');
        }
        $filename = basename($name);
        $target = $stage . '/' . $filename;
        file_put_contents($target, $bytes);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($target);
        $allowed = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
        if ($mime !== $allowed[pathinfo($filename, PATHINFO_EXTENSION)]) {
            throw new RuntimeException('Uploaded file type does not match its extension.');
        }
        $staged[$filename] = $target;
    }
    foreach ($data['tables']['media'] as $media) {
        if (str_starts_with($media['url'], 'uploads/') && !isset($staged[basename($media['url'])])) {
            throw new RuntimeException('A media record references a missing archive file.');
        }
    }
    if (!$apply) {
        echo 'Archive validated: ' . count($tables) . ' tables and ' . count($staged) . " uploads. No data changed.\n";
    }
    else {
        if (!is_dir($uploads)) {
            mkdir($uploads, 0755, true);
        }
        db()->beginTransaction();
        $versions = array_column(all('SELECT id,session_version FROM users'), 'session_version', 'id');
        foreach (array_reverse($tables) as $table) {
            query('DELETE FROM ' . $table);
        }
        foreach ($tables as $table) {
            $columns = $schema[$table];
            $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
            foreach ($data['tables'][$table] as $row) {
                if ($table === 'users') {
                    $row['session_version'] = max((int)$row['session_version'], (int)($versions[$row['id']] ?? 0)) + 1;
                }
                query($sql, array_map(fn($column) => $row[$column], $columns));
            }
        }
        foreach ($staged as $filename => $file) {
            $destination = $uploads . '/' . $filename;
            if (is_link($destination)) {
                throw new RuntimeException('Refusing to overwrite a symbolic link.');
            }
            $originals[$filename] = is_file($destination) ? file_get_contents($destination) : null;
            if (!copy($file, $destination)) {
                throw new RuntimeException('Unable to restore upload.');
            }
        }
        db()->commit();
        echo "Database and media restored. Existing sessions revoked. Unreferenced existing uploads were retained.\n";
    }
}
catch (Throwable $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    foreach ($originals as $filename => $bytes) {
        if ($bytes === null) {
            unlink($uploads . '/' . $filename);
        }
        else {
            file_put_contents($uploads . '/' . $filename, $bytes);
        }
    }
    throw $e;
}
finally {
    $zip->close();
    foreach (glob($stage . '/*') as $file) {
        unlink($file);
    }
    rmdir($stage);
}
