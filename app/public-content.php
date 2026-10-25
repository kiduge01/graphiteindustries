<?php
declare(strict_types=1);
require_once __DIR__ . '/legal.php';
function published_news(int $page, ?int $postId = null): array
{
    $total = (int)query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
    $pages = max(1, (int)ceil($total / 20));
    $offset = (min($page, $pages) - 1) * 20;
    $posts = all("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC,id DESC LIMIT 20 OFFSET $offset");
    $post = $postId ? query("SELECT * FROM posts WHERE id=? AND status='published'", [$postId])->fetch() : null;
    return ['posts' => $posts, 'post' => $post ?: null, 'page' => $page, 'pages' => $pages, 'total' => $total];
}
function public_url(string $query = ''): string
{
    global $config;
    return rtrim($config['base_url'], '/') . '/' . ($query !== '' ? '?' . $query : '');
}
function html_escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function body_paragraphs(string $body): string
{
    return implode('', array_map(fn($p) => '<p>' . nl2br(html_escape($p)) . '</p>', preg_split('/\n\s*\n/', $body)));
}
