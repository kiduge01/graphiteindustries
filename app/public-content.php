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
// Absolute clean route: base/ for home, base/about, base/news?post=3.
function page_url(string $id, array $params = []): string
{
    global $config;
    $path = $id === 'home' ? '' : rawurlencode($id);
    return rtrim($config['base_url'], '/') . '/' . $path . ($params ? '?' . http_build_query($params) : '');
}
function asset_url(string $path): string
{
    global $config;
    return preg_match('~^https://~', $path) ? $path : rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
}
// Open Graph, Twitter card and Organization structured data for one page.
function social_meta(array $page, array $settings, string $title, string $canonical): string
{
    $company = $settings['company'] ?? 'Graphite Industries Limited';
    $image = $page['image'] ?? '';
    // Social networks need JPEG/PNG and a landscape share image works best everywhere.
    $image = preg_match('~^(assets|uploads)/[a-zA-Z0-9._/-]+\.(jpe?g|png)$~', $image) ? $image : 'assets/og-image.jpg';
    $tags = [
        'og:type' => isset($page['published_at']) ? 'article' : 'website',
        'og:site_name' => $company,
        'og:title' => $title,
        'og:description' => $page['summary'] ?? '',
        'og:url' => $canonical,
        'og:image' => asset_url($image),
        'og:locale' => 'en_GB',
    ];
    $html = '';
    foreach ($tags as $property => $content) {
        $html .= '<meta property="' . $property . '" content="' . html_escape($content) . '">';
    }
    if ($image === 'assets/og-image.jpg') {
        $html .= '<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">';
    }
    $html .= '<meta property="og:image:alt" content="' . html_escape($company) . '">';
    $html .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . html_escape($title) . '"><meta name="twitter:description" content="' . html_escape($page['summary'] ?? '') . '"><meta name="twitter:image" content="' . html_escape(asset_url($image)) . '">';
    $sameAs = array_values(array_filter(array_map(fn($key) => $settings[$key] ?? '', ['linkedin', 'x', 'facebook', 'instagram', 'youtube']), fn($url) => preg_match('~^https://[^\s"\'<>]+$~', $url)));
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $company,
        'url' => page_url('home'),
        'logo' => asset_url('assets/logo-512.png'),
        'email' => $settings['email'] ?? '',
        'telephone' => $settings['phone'] ?? '',
        'slogan' => $settings['tagline'] ?? '',
        'address' => ($settings['address'] ?? '') !== '' ? ['@type' => 'PostalAddress', 'streetAddress' => $settings['address'], 'addressCountry' => 'TZ'] : null,
        'sameAs' => $sameAs ?: null,
    ]);
    $json = json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    return $html . '<script type="application/ld+json">' . $json . '</script>';
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
