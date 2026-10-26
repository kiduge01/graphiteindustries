<?php
declare(strict_types=1);
$template = file_get_contents(__DIR__ . '/index.html');
try {
    require __DIR__ . '/../app/bootstrap.php';
    require ROOT . '/app/public-content.php';
    require ROOT . '/app/public-presentation.php';
    security_headers();
    header('Content-Type: text/html; charset=utf-8');
    $id = $_GET['page'] ?? 'home';
    if ($id !== 'home') {
        $template = str_replace('media="not all"', 'media="all"', $template);
    }
    if (!is_string($id) || !preg_match('/^[a-z][a-z0-9-]{0,39}$/', $id)) {
        $id = '__invalid';
    }
    $settings = array_column(all('SELECT * FROM settings'), 'setting_value', 'setting_key');
    $pages = all("SELECT * FROM pages WHERE status='published'");
    $page = null;
    foreach ($pages as $candidate) {
        if ($candidate['id'] === $id) {
            $page = $candidate;
        }
    }
    $legal = legal_pages($settings['email'] ?? '');
    $canonical = 'page=' . rawurlencode($id);
    $extra = '';
    if (isset($legal[$id])) {
        $page = $legal[$id];
    }
    elseif ($id === 'news') {
        $number = filter_var($_GET['newsPage'] ?? '1', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
        $postId = isset($_GET['post']) ? filter_var($_GET['post'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
        $news = $number && $postId !== false ? published_news($number, $postId) : null;
        if ($news && $number <= $news['pages'] && ($postId === null || $news['post'])) {
            if ($postId) {
                $page = $news['post'];
                $canonical .= '&post=' . $postId;
            }
            else {
                $page = ['title' => 'News & Updates', 'summary' => 'Company announcements and verified project developments.', 'body' => ''];
                foreach ($news['posts'] as $post) {
                    $extra .= '<article><h2><a href="?page=news&amp;post=' . (int)$post['id'] . '">' . html_escape($post['title']) . '</a></h2><p>' . html_escape($post['summary']) . '</p></article>';
                }
                if (!$news['posts']) {
                    $extra = '<p>There are no published updates yet. Contact us for current project information.</p>';
                }
                if ($number > 1) {
                    $canonical .= '&newsPage=' . $number;
                    $extra .= '<a href="?page=news&amp;newsPage=' . ($number - 1) . '">Previous updates</a> ';
                }
                if ($number < $news['pages']) {
                    $extra .= '<a href="?page=news&amp;newsPage=' . ($number + 1) . '">Next updates</a>';
                }
            }
        }
    }
    if (!$page) {
        http_response_code(404);
        header('X-Robots-Tag: noindex');
        $page = ['title' => 'Page not found', 'summary' => 'This page is unavailable or has not been published.', 'body' => 'Use the navigation to explore available company information.'];
    }
    else {
        $template = str_replace('</head>', '<link rel="canonical" href="' . html_escape(public_url($canonical)) . '"></head>', $template);
    }
    $title = $page['seo'] ?? $page['title'] . ' | Graphite Industries Limited';
    if ($title === '') {
        $title = $page['title'];
    }
    $template = preg_replace_callback('~<title>.*?</title>~s', fn() => '<title>' . html_escape($title) . '</title>', $template);
    $template = preg_replace_callback('~<meta name="description"[^>]*>~', fn() => '<meta name="description" content="' . html_escape($page['summary']) . '">', $template);
    $article = '<article class="section article-copy"><h1>' . html_escape($page['title']) . '</h1><p class="lead">' . html_escape($page['summary']) . '</p>' . body_paragraphs($page['body']) . $extra . '</article>';
    if ($id === 'home' && http_response_code() !== 404) {
        $article = server_home($page);
    } elseif (in_array($id, ['about', 'project', 'products', 'investors', 'sustainability', 'contact'], true) && http_response_code() !== 404) {
        $article = server_editorial($page, $id, $settings);
    }
    $template = str_replace('<main id="main" tabindex="-1"></main>', '<main id="main" tabindex="-1">' . $article . '</main>', $template);
    $navigation = '';
    $labels = ['home' => 'Home', 'about' => 'About Us', 'project' => 'Our Project', 'products' => 'Products & Applications', 'sustainability' => 'Sustainability', 'investors' => 'Investors & Partners', 'contact' => 'Contact Us'];
    foreach ($pages as $item) {
        $navigation .= '<a' . ($item['id'] === $id ? ' class="active" aria-current="page"' : '') . ' href="?page=' . html_escape($item['id']) . '">' . html_escape($labels[$item['id']] ?? ucfirst($item['id'])) . '</a>';
    }
    $navigation .= '<a href="?page=news">News &amp; Updates</a><a href="?page=privacy">Privacy notice</a><a href="?page=terms">Website terms</a>';
    $template = str_replace('<nav id="site-nav" aria-label="Main navigation"></nav>', '<nav id="site-nav" aria-label="Main navigation">' . $navigation . '</nav>', $template);
    $template = str_replace('<footer id="footer"></footer>', '<footer id="footer"><p>' . html_escape($settings['company'] ?? 'Graphite Industries Limited') . '</p><p>' . html_escape($settings['email'] ?? '') . '</p></footer>', $template);
}
catch (Throwable $e) {
    error_log('Graphite page: ' . $e->getMessage());
    http_response_code(503);
    header('Retry-After: 300');
    $template = str_replace('<main id="main" tabindex="-1"></main>', '<main id="main" tabindex="-1"><section class="section"><h1>Temporarily unavailable</h1><p>Please try again later.</p></section></main>', $template);
}
echo $template;
