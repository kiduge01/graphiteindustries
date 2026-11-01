<?php
declare(strict_types=1);
// Relative destination supports both a domain root and a WAMP subdirectory.
// ?page=about goes straight to the clean public route public/about.
$destination = 'public/';
$params = $_GET;
$page = $params['page'] ?? null;
if (is_string($page) && preg_match('/^[a-z][a-z0-9-]{0,39}$/', $page)) {
    unset($params['page']);
    $destination .= $page === 'home' ? '' : $page;
}
if ($params) {
    $destination .= '?' . http_build_query($params);
}
header('Cache-Control: no-store');
header('Location: ' . $destination, true, 302);
exit;
