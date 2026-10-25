<?php
declare(strict_types=1);
header('Content-Type: application/xml; charset=utf-8');
try {
    require __DIR__ . '/../app/bootstrap.php';
    require ROOT . '/app/public-content.php';
    $urls = [];
    foreach (all("SELECT id,updated_at FROM pages WHERE status='published'") as $page) {
        $urls[] = ['query' => 'page=' . rawurlencode($page['id']), 'modified' => $page['updated_at']];
    }
    foreach (['news', 'privacy', 'terms'] as $id) {
        $urls[] = ['query' => 'page=' . $id];
    }
    $count = (int)query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
    for ($number = 2; $number <= (int)ceil($count / 20); $number++) {
        $urls[] = ['query' => 'page=news&newsPage=' . $number];
    }
    foreach (all("SELECT id,updated_at FROM posts WHERE status='published'") as $post) {
        $urls[] = ['query' => 'page=news&post=' . (int)$post['id'], 'modified' => $post['updated_at']];
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url><loc>' . htmlspecialchars(public_url($url['query']), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
        if (!empty($url['modified'])) {
            $xml .= '<lastmod>' . htmlspecialchars(substr($url['modified'], 0, 10), ENT_XML1, 'UTF-8') . '</lastmod>';
        }
        $xml .= '</url>';
    }
    echo $xml . '</urlset>';
}
catch (Throwable $e) {
    error_log('Graphite sitemap: ' . $e->getMessage());
    http_response_code(503);
}
