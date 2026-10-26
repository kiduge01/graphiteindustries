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
    $html = '';
    foreach (preg_split('/\n\s*\n/', trim($body)) as $block) {
        $lines = explode("\n", $block);
        if (preg_match('/^(#{2,3}) (.+)$/', $lines[0], $heading)) {
            $tag = strlen($heading[1]) === 2 ? 'h2' : 'h3';
            $html .= '<' . $tag . '>' . html_escape($heading[2]) . '</' . $tag . '>';
            array_shift($lines);
            if (!$lines) continue;
            $block = implode("\n", $lines);
        }
        if (count(array_filter($lines, fn($line) => str_starts_with($line, '- '))) === count($lines)) {
            $html .= '<ul class="copy-list">' . implode('', array_map(fn($line) => '<li>' . html_escape(substr($line, 2)) . '</li>', $lines)) . '</ul>';
        } elseif (count(array_filter($lines, fn($line) => preg_match('/^\d+\. /', $line))) === count($lines)) {
            $html .= '<ol class="copy-list">';
            foreach ($lines as $line) {
                $text = preg_replace('/^\d+\. /', '', $line);
                $parts = explode(' — ', $text, 2);
                $html .= '<li>' . (count($parts) === 2 ? '<h3>' . html_escape($parts[0]) . '</h3><p>' . html_escape($parts[1]) . '</p>' : html_escape($text)) . '</li>';
            }
            $html .= '</ol>';
        } else {
            $html .= '<p>' . nl2br(html_escape($block)) . '</p>';
        }
    }
    return $html;
}
