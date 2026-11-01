<?php
declare(strict_types=1);
$template = file_get_contents(__DIR__ . '/index.html');
try {
    require __DIR__ . '/../app/bootstrap.php';
    require ROOT . '/app/public-content.php';
    require ROOT . '/app/public-presentation.php';
    require ROOT . '/app/visual-pages.php';
    security_headers();
    header('Content-Type: text/html; charset=utf-8');
    $id = $_GET['page'] ?? 'home';
    // Old ?page= links (bookmarks, search results) move permanently to clean routes.
    // Direct index.php requests are left alone so hosts without rewriting still work.
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (isset($_GET['page']) && is_string($id) && str_ends_with($requestPath, '/') && preg_match('/^[a-z][a-z0-9-]{0,39}$/', $id)
        && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        $params = $_GET;
        unset($params['page']);
        header('Location: ' . page_url($id, $params), true, 301);
        exit;
    }
    if ($id !== 'home') {
        $template = str_replace('media="not all"', 'media="all"', $template);
    }
    if (!is_string($id) || !preg_match('/^[a-z][a-z0-9-]{0,39}$/', $id)) {
        $id = '__invalid';
    }
    $settings = array_column(all('SELECT * FROM settings'), 'setting_value', 'setting_key');
    unset($settings['notify_email']);
    $pages = all("SELECT * FROM pages WHERE status='published'");
    $page = null;
    foreach ($pages as $candidate) {
        if ($candidate['id'] === $id) {
            $page = $candidate;
        }
    }
    $legal = legal_pages($settings['email'] ?? '');
    $canonical = page_url($id);
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
                $canonical = page_url('news', ['post' => $postId]);
                $newsHtml = server_news_article($news['post'], published_news(1)['posts']);
            }
            else {
                $page = ['title' => 'News & Updates', 'summary' => 'Company announcements and verified project developments.', 'body' => ''];
                if ($number > 1) {
                    $canonical = page_url('news', ['newsPage' => $number]);
                }
                $newsHtml = server_news_list($news);
            }
        }
    }
    if (!$page) {
        http_response_code(404);
        header('X-Robots-Tag: noindex');
        $page = ['title' => 'Page not found', 'summary' => 'This page is unavailable or has not been published.', 'body' => 'Use the navigation to explore available company information.'];
    }
    $title = $page['seo'] ?? $page['title'] . ' | Graphite Industries Limited';
    if ($title === '') {
        $title = $page['title'];
    }
    if (http_response_code() !== 404) {
        $template = str_replace('</head>', '<link rel="canonical" href="' . html_escape($canonical) . '">' . social_meta($page, $settings, $title, $canonical) . '</head>', $template);
    }
    $template = str_replace('<html lang="en" class="no-js">', '<html lang="en" class="no-js" data-page="' . html_escape($id) . '">', $template);
    $template = preg_replace_callback('~<title>.*?</title>~s', fn() => '<title>' . html_escape($title) . '</title>', $template);
    $template = preg_replace_callback('~<meta name="description"[^>]*>~', fn() => '<meta name="description" content="' . html_escape($page['summary']) . '">', $template);
    $notFound = http_response_code() === 404;
    if ($notFound) {
        $article = server_title_block('PAGE UNAVAILABLE', 'This page is not published.', 'Use the navigation to explore the available company information.');
    } elseif (isset($legal[$id])) {
        $article = server_title_block('WEBSITE INFORMATION', $page['title'], $page['summary']) . '<section class="section legal">' . body_paragraphs($page['body']) . ($id === 'privacy' ? '<button class="btn secondary" id="reset-consent" type="button">Change analytics preference</button>' : '') . '</section>';
    } elseif ($id === 'home') {
        $article = server_home($page);
    } elseif (isset($newsHtml)) {
        $article = $newsHtml;
    } else {
        $article = server_editorial($page, $id, $settings);
    }
    $template = str_replace('<main id="main" tabindex="-1"></main>', '<main id="main" tabindex="-1">' . $article . '</main>', $template);
    $template = str_replace('<nav id="site-nav" aria-label="Main navigation"></nav>', '<nav id="site-nav" aria-label="Main navigation">' . server_navigation($pages, $id) . '</nav>', $template);
    $template = str_replace('<footer id="footer"></footer>', '<footer id="footer">' . server_footer($settings, !$notFound && in_array($id, ['products', 'sustainability', 'investors', 'contact', 'careers', 'news'], true), !$notFound && $id === 'home') . '</footer>', $template);
    $template = str_replace('<body>', '<body data-page="' . html_escape($notFound ? '__missing' : $id) . '">', $template);
}
catch (Throwable $e) {
    error_log('Graphite page: ' . $e->getMessage());
    http_response_code(503);
    header('Retry-After: 300');
    $template = str_replace('<main id="main" tabindex="-1"></main>', '<main id="main" tabindex="-1"><section class="section"><h1>Temporarily unavailable</h1><p>Please try again later.</p></section></main>', $template);
}
echo $template;
