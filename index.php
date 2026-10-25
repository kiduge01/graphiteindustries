<?php
declare(strict_types=1);
// Relative destination supports both a domain root and a WAMP subdirectory.
$destination = 'public/';
$query = $_SERVER['QUERY_STRING'] ?? '';
if ($query !== '') {
    $destination .= '?' . str_replace(["\r", "\n"], '', $query);
}
header('Cache-Control: no-store');
header('Location: ' . $destination, true, 302);
exit;
