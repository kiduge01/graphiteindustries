<?php
declare(strict_types=1);

// Image-led page designs for Investors, Contact, Careers and News, built from the
// same blocks as the Sustainability page (sv-* classes in sustainability-page.css).
// Each page root carries data-layout so the browser keeps this markup instead of
// re-rendering it; all CMS text stays editable and escaped.

// Splits CMS text into "## " sections. Within a section, "- Title — text" and
// "1. Title — text" lines and "### Title" + paragraph blocks become items;
// remaining paragraphs become notes.
function visual_sections(string $body): array
{
    $parts = preg_split('/^## /m', $body);
    $sections = ['' => ['items' => [], 'notes' => array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', trim(array_shift($parts))))))]];
    foreach ($parts as $part) {
        [$title, $text] = array_pad(explode("\n", $part, 2), 2, '');
        $items = []; $notes = [];
        foreach (preg_split('/^### /m', trim($text)) as $n => $chunk) {
            if ($n > 0) {
                [$heading, $copy] = array_pad(explode("\n", $chunk, 2), 2, '');
                $items[] = [trim($heading), trim(preg_replace('/\s+/', ' ', $copy))];
                continue;
            }
            foreach (preg_split('/\n\s*\n/', trim($chunk)) as $block) {
                $lines = array_values(array_filter(explode("\n", trim($block)), fn($l) => trim($l) !== ''));
                if ($lines && count(array_filter($lines, fn($l) => preg_match('/^(- |\d+\. )/', $l))) === count($lines)) {
                    foreach ($lines as $line) $items[] = array_pad(explode(' — ', preg_replace('/^(- |\d+\. )/', '', $line), 2), 2, '');
                } elseif (trim($block) !== '') $notes[] = trim($block);
            }
        }
        $sections[trim($title)] = ['items' => $items, 'notes' => $notes];
    }
    return $sections;
}

function vp_slug(string $text): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(str_replace('&', 'and', $text))), '-');
}

// Local photo with responsive WebP sources when an -800/-1600 pair exists.
function vp_image(string $src, string $alt, int $width, int $height, bool $priority = false, string $sizes = '(max-width:1000px) 100vw, 50vw'): string
{
    $srcset = '';
    if (preg_match('~^(assets/(?:pages|sustainability)/[a-z0-9-]+)-1600\.webp$~', $src, $m)) {
        $srcset = ' srcset="' . $m[1] . '-800.webp 800w, ' . $src . ' ' . $width . 'w" sizes="' . $sizes . '"';
    }
    return '<img src="' . html_escape($src) . '"' . $srcset . ' alt="' . html_escape($alt) . '" width="' . $width . '" height="' . $height . '"' . ($priority ? ' fetchpriority="high"' : ' loading="lazy" decoding="async"') . '>';
}

function vp_hero(array $page, string $image, string $caption = '', array $jump = []): string
{
    $links = '';
    foreach ($jump as $anchor => $label) $links .= '<a href="#' . html_escape($anchor) . '">' . html_escape($label) . '</a>';
    return '<section class="sv-hero"><div class="sv-hero-copy"><a class="breadcrumb" href="./">Home /</a><span class="kicker">' . html_escape($page['eyebrow'] ?? '') . '</span><h1>' . html_escape($page['title']) . '</h1><p class="lead">' . html_escape($page['summary']) . '</p>'
        . ($links ? '<nav class="sv-jump" aria-label="On this page">' . $links . '</nav>' : '') . '</div><figure class="sv-hero-image">' . $image
        . ($caption !== '' ? '<figcaption>' . html_escape($caption) . '</figcaption>' : '') . '</figure></section>';
}

function vp_chips(array $items): string
{
    return '<ul class="sv-chips">' . implode('', array_map(fn($i) => '<li data-icon="' . vp_slug($i[0]) . '">' . html_escape($i[0]) . '</li>', $items)) . '</ul>';
}

function vp_details(array $section, string $label = 'How we will approach this'): string
{
    if (!$section['items'] && !$section['notes']) return '';
    $list = $section['items'] ? '<dl>' . implode('', array_map(fn($i) => '<dt>' . html_escape($i[0]) . '</dt>' . ($i[1] !== '' ? '<dd>' . html_escape($i[1]) . '</dd>' : ''), $section['items'])) . '</dl>' : '';
    return '<details class="sv-details"><summary>' . html_escape($label) . '</summary>' . $list . implode('', array_map(fn($n) => '<p>' . html_escape($n) . '</p>', $section['notes'])) . '</details>';
}

function vp_tiles(array $items): string
{
    return '<div class="vp-tiles">' . implode('', array_map(fn($i) => '<article class="vp-tile" data-icon="' . vp_slug($i[0]) . '"><h3>' . html_escape($i[0]) . '</h3><p>' . html_escape($i[1]) . '</p></article>', $items)) . '</div>';
}

function vp_cta(string $kicker, string $title, string $href, string $label): string
{
    return '<section class="next-chapter"><div><span class="kicker">' . html_escape($kicker) . '</span><h2>' . html_escape($title) . '</h2></div><a class="chapter-link" href="' . html_escape($href) . '">' . html_escape($label) . ' ↗</a></section>';
}

function vp_page_image(array $page, string $fallback): string
{
    $image = $page['image'] ?? '';
    return preg_match('~^(assets|uploads)/[a-zA-Z0-9._/-]+$~', $image) && !str_contains($image, '..') ? $image : $fallback;
}

function server_investors(array $page, array $settings): string
{
    $s = visual_sections($page['body']);
    $empty = ['items' => [], 'notes' => []];
    $why = $s['Why this opportunity'] ?? $empty;
    $seek = $s['Contributions we seek'] ?? $empty;
    $steps = $s['How a conversation develops'] ?? $empty;
    $info = $s['Project information'] ?? $empty;
    $notice = $s['Important notice'] ?? $empty;
    $hero = vp_image(vp_page_image($page, 'assets/port.jpg'), 'Container port and cargo handling', 1287, 860, true);
    $html = '<div class="sustain-page visual-page" data-layout="investors">' . vp_hero($page, $hero, 'Regional and global markets', ['vp-why' => 'Why invest', 'vp-seek' => 'Contributions', 'vp-engage' => 'How we engage']);
    if ($s['']['notes']) $html .= '<section class="section sv-statement"><p>' . html_escape(implode(' ', $s['']['notes'])) . '</p></section>';
    if ($why['items']) $html .= '<section class="section vp-block" id="vp-why"><span class="kicker">WHY THIS OPPORTUNITY</span><h2>Graphite value, built in Tanzania.</h2>' . vp_tiles($why['items']) . '</section>';
    if ($seek['items']) $html .= '<section class="section sv-pillar" id="vp-seek"><figure class="sv-pillar-image">' . vp_image('assets/pages/partnership-1600.webp', 'Two business partners shaking hands in an office', 1600, 1067) . '</figure><div class="sv-pillar-copy"><span class="kicker">CONTRIBUTIONS WE SEEK</span><h2>Expertise that moves the project forward.</h2>' . vp_chips($seek['items']) . vp_details($seek, 'What each contribution involves') . '</div></section>';
    if ($steps['items']) $html .= '<section class="sv-banner" id="vp-engage" aria-labelledby="vp-engage-title"><img src="assets/graphite.jpg" alt="Close-up of a graphite mineral specimen" width="1290" height="860" loading="lazy" decoding="async"><div class="sv-banner-copy"><span class="kicker">HOW WE ENGAGE</span><h2 id="vp-engage-title">Three steps to a partnership.</h2><ol class="sv-steps">' . implode('', array_map(fn($i) => '<li><h3>' . html_escape($i[0]) . '</h3><p>' . html_escape($i[1]) . '</p></li>', $steps['items'])) . '</ol>' . ($steps['notes'] ? '<p class="vp-banner-note">' . html_escape(implode(' ', $steps['notes'])) . '</p>' : '') . '</div></section>';
    $html .= '<section class="section vp-info"><article class="vp-card"><span class="kicker">PROJECT INFORMATION</span><h2>Request the project brief.</h2>' . implode('', array_map(fn($n) => '<p>' . html_escape($n) . '</p>', $info['notes'])) . '<a class="btn green" href="contact?category=Investment&amp;topic=project-information">Request information <span aria-hidden="true">↗</span></a></article>'
        . ($notice['notes'] ? '<aside class="vp-notice"><h2>Important notice</h2>' . implode('', array_map(fn($n) => '<p>' . html_escape($n) . '</p>', $notice['notes'])) . '</aside>' : '') . '</section>';
    foreach ($s as $title => $section) {
        if (in_array($title, ['', 'Why this opportunity', 'Contributions we seek', 'How a conversation develops', 'Project information', 'Important notice'], true)) continue;
        $html .= '<section class="section editorial-section"><h2>' . html_escape($title) . '</h2><div class="editorial-body">' . vp_details($section, 'Read more') . '</div></section>';
    }
    return $html . company_profile_section($settings) . vp_cta('A PLACE TO BEGIN', 'Bring your experience to the conversation.', 'contact?category=Investment', 'Introduce your organisation') . '</div>';
}

function server_contact(array $page, array $settings): string
{
    $hero = vp_image('assets/pages/dar-es-salaam-1600.webp', 'Dar es Salaam city skyline seen across the harbour', 1024, 768, true);
    $phone = $settings['phone'] ?? '';
    $map = map_link($settings);
    $cards = '<article class="vp-contact" data-icon="email"><h3>Email</h3><a href="mailto:' . html_escape($settings['email'] ?? '') . '">' . html_escape($settings['email'] ?? '') . '</a></article>'
        . ($phone !== '' ? '<article class="vp-contact" data-icon="phone"><h3>Phone</h3><a href="tel:' . html_escape(str_replace(' ', '', $phone)) . '">' . html_escape($phone) . '</a></article>' : '')
        . '<article class="vp-contact" data-icon="head-office"><h3>Head office</h3><p>' . html_escape($settings['address'] ?? '') . '</p>' . ($map ? '<a href="' . html_escape($map) . '" rel="noopener" target="_blank">View on map ↗</a>' : '') . '</article>'
        . '<article class="vp-contact" data-icon="project-location"><h3>Project location</h3><p>Mpwapwa District, Dodoma Region, Tanzania</p>' . (($settings['office_hours'] ?? '') !== '' ? '<p class="vp-hours">' . html_escape($settings['office_hours']) . '</p>' : '') . '</article>';
    $intro = implode('', array_map(fn($p) => '<p>' . html_escape($p) . '</p>', array_filter(array_map('trim', preg_split('/\n\s*\n/', trim($page['body']))))));
    return '<div class="sustain-page visual-page" data-layout="contact">' . vp_hero($page, $hero, 'Head office · Dar es Salaam')
        . '<section class="section vp-contacts" aria-label="Contact details">' . $cards . '</section>'
        . '<section class="section vp-enquiry" id="enquiry"><div class="vp-enquiry-copy"><span class="kicker">SEND AN ENQUIRY</span><h2>Tell us what you would like to explore.</h2>' . $intro . '<ul class="vp-checks"><li>Investment and partnerships</li><li>Product requirements</li><li>Technology and engineering</li><li>Supply and logistics</li></ul></div>'
        . '<div class="vp-form-slot" id="contact-form-slot"><p class="panel">Enable JavaScript to use the enquiry form, or email <a href="mailto:' . html_escape($settings['email'] ?? '') . '">' . html_escape($settings['email'] ?? '') . '</a>.</p></div></section></div>';
}

function server_careers(array $page): string
{
    $s = visual_sections($page['body']);
    $empty = ['items' => [], 'notes' => []];
    $skills = $s['Expertise we expect to need'] ?? $empty;
    $current = $s['Current opportunities'] ?? $empty;
    $interest = $s['Expressions of interest'] ?? $empty;
    $fair = $s['Fair recruitment'] ?? $empty;
    $hero = vp_image('assets/pages/laboratory-1600.webp', 'Laboratory technician testing samples in coloured test tubes', 1600, 2398, true);
    $paragraphs = fn(array $sec) => implode('', array_map(fn($n) => '<p>' . html_escape($n) . '</p>', $sec['notes']));
    $html = '<div class="sustain-page visual-page" data-layout="careers">' . vp_hero($page, $hero, '', ['vp-skills' => 'Expertise', 'vp-roles' => 'Opportunities']);
    if ($s['']['notes']) $html .= '<section class="section sv-statement"><p>' . html_escape(implode(' ', $s['']['notes'])) . '</p></section>';
    if ($skills['items']) $html .= '<section class="section vp-block" id="vp-skills"><span class="kicker">EXPERTISE WE EXPECT TO NEED</span><h2>Skills that will build the project.</h2>' . vp_chips($skills['items']) . '</section>';
    $html .= '<section class="section vp-info" id="vp-roles"><article class="vp-card"><span class="vp-status">No vacancies advertised</span><h2>Current opportunities</h2>' . $paragraphs($current) . '</article><article class="vp-card vp-card-green"><span class="kicker">EXPRESSIONS OF INTEREST</span><h2>Introduce yourself.</h2>' . $paragraphs($interest) . '<a class="btn" href="contact?category=General&amp;topic=careers">Get in touch <span aria-hidden="true">↗</span></a></article></section>';
    if ($fair['notes']) $html .= '<section class="section"><aside class="vp-notice vp-notice-wide" data-icon="fair-recruitment"><h2>Fair recruitment</h2>' . $paragraphs($fair) . '</aside></section>';
    return $html . '</div>';
}

// Illustrative images for news posts that have no image of their own.
function news_fallback_image(int $id): string
{
    $images = ['assets/sustainability/mpwapwa-landscape-800.webp', 'assets/graphite.jpg', 'assets/logistics.jpg', 'assets/pages/partnership-800.webp'];
    return $images[$id % count($images)];
}

function news_card(array $post): string
{
    $image = preg_match('~^(assets|uploads)/[a-zA-Z0-9._/-]+$~', $post['image'] ?? '') ? $post['image'] : news_fallback_image((int)$post['id']);
    return '<article class="vp-news-card"><a href="news?post=' . (int)$post['id'] . '"><div class="vp-news-image"><img src="' . html_escape($image) . '" alt="" loading="lazy" decoding="async"></div><div class="vp-news-copy"><time datetime="' . html_escape(substr((string)$post['published_at'], 0, 10)) . '">' . html_escape(date('j F Y', strtotime((string)$post['published_at']) ?: time())) . '</time><h3>' . html_escape($post['title']) . '</h3><p>' . html_escape($post['summary']) . '</p><span>Read update ↗</span></div></a></article>';
}

function server_news_list(array $news): string
{
    $page = ['eyebrow' => 'NEWS & PROJECT UPDATES', 'title' => 'Progress, shared with clarity.', 'summary' => 'Company announcements and verified project developments.'];
    $hero = vp_image('assets/sustainability/mpwapwa-landscape-1600.webp', 'Green plains and hills in Mpwapwa District, Dodoma Region', 1600, 1067, true);
    $cards = implode('', array_map('news_card', $news['posts']));
    $pager = '';
    if ($news['pages'] > 1) {
        $pager = '<nav class="section pagination" aria-label="News pages">' . ($news['page'] > 1 ? '<a class="btn secondary" href="news?newsPage=' . ($news['page'] - 1) . '">Previous</a>' : '') . '<span>Page ' . (int)$news['page'] . ' of ' . (int)$news['pages'] . '</span>' . ($news['page'] < $news['pages'] ? '<a class="btn secondary" href="news?newsPage=' . ($news['page'] + 1) . '">Next</a>' : '') . '</nav>';
    }
    return '<div class="sustain-page visual-page" data-layout="news">' . vp_hero($page, $hero, 'Mpwapwa District, Dodoma')
        . '<section class="section vp-block">' . ($cards ? '<div class="vp-news-grid">' . $cards . '</div>' : '<p class="vp-empty">There are no published updates yet. Subscribe below to receive the first announcements.</p>') . '</section>' . $pager . '</div>';
}

function server_news_article(array $post, array $others): string
{
    $image = preg_match('~^(assets|uploads)/[a-zA-Z0-9._/-]+$~', $post['image'] ?? '') ? $post['image'] : news_fallback_image((int)$post['id']);
    $body = implode('', array_map(fn($p) => '<p>' . nl2br(html_escape($p)) . '</p>', array_filter(array_map('trim', preg_split('/\n\s*\n/', trim($post['body']))))));
    $more = implode('', array_map('news_card', array_slice(array_values(array_filter($others, fn($o) => (int)$o['id'] !== (int)$post['id'])), 0, 2)));
    return '<div class="sustain-page visual-page" data-layout="news"><article class="vp-article"><header class="sv-hero"><div class="sv-hero-copy"><a class="breadcrumb" href="news">News &amp; Updates /</a><time class="kicker" datetime="' . html_escape(substr((string)$post['published_at'], 0, 10)) . '">' . html_escape(date('j F Y', strtotime((string)$post['published_at']) ?: time())) . '</time><h1>' . html_escape($post['title']) . '</h1><p class="lead">' . html_escape($post['summary']) . '</p></div><figure class="sv-hero-image"><img src="' . html_escape($image) . '" alt="" fetchpriority="high"></figure></header>'
        . '<div class="section vp-article-body">' . $body . '<a class="text-link" href="news">All updates <span>↗</span></a></div></article>'
        . ($more ? '<section class="section vp-block"><span class="kicker">MORE UPDATES</span><div class="vp-news-grid vp-news-two">' . $more . '</div></section>' : '') . '</div>';
}

function vp_paragraphs(array $notes): string
{
    return implode('', array_map(fn($n) => '<p>' . html_escape($n) . '</p>', $notes));
}

function vp_icon_steps(array $items, string $class): string
{
    return '<ol class="' . $class . '">' . implode('', array_map(fn($i) => '<li data-icon="' . vp_slug($i[0]) . '"><h3>' . html_escape($i[0]) . '</h3></li>', $items)) . '</ol>';
}

function server_about_visual(array $page): string
{
    $s = visual_sections($page['body']);
    $empty = ['items' => [], 'notes' => []];
    $vision = $s['Our vision'] ?? $empty;
    $mission = $s['Our mission'] ?? $empty;
    $objectives = $s['Our strategic objectives'] ?? $empty;
    $develop = $s['How we intend to develop'] ?? $empty;
    $values = $s['Our values'] ?? $empty;
    $hero = vp_image(vp_page_image($page, 'assets/graphite.jpg'), 'Graphite mineral specimen', 1290, 860, true);
    // The approved leadership showcase (team photos and spotlight), kept as its own template.
    $leadership = file_get_contents(__DIR__ . '/leadership.html');
    $html = '<div class="sustain-page visual-page" data-layout="about">' . vp_hero($page, $hero, 'Graphite Industries Limited · Tanzania', ['vp-purpose' => 'Purpose', 'vp-values' => 'Values', 'leadership' => 'Leadership']);
    if ($s['']['notes']) $html .= '<section class="section sv-statement"><p>' . html_escape(implode(' ', $s['']['notes'])) . '</p></section>';
    $html .= '<section class="section vp-info vp-purpose" id="vp-purpose"><article class="vp-card vp-card-green"><span class="kicker">OUR VISION</span>' . vp_paragraphs($vision['notes']) . '</article><article class="vp-card"><span class="kicker">OUR MISSION</span>' . vp_paragraphs($mission['notes']) . '</article></section>';
    if ($values['items']) $html .= '<section class="section vp-block" id="vp-values"><span class="kicker">OUR VALUES</span><h2>What guides our decisions.</h2>' . vp_chips($values['items']) . ($values['notes'] ? '<p class="vp-lead-note">' . html_escape(implode(' ', $values['notes'])) . '</p>' : '') . '</section>';
    if ($objectives['items']) $html .= '<section class="section sv-pillar"><figure class="sv-pillar-image">' . vp_image('assets/project-site.webp', 'Mining equipment and workers at the project site', 1280, 1280) . '<figcaption class="vp-figcaption">Project site</figcaption></figure><div class="sv-pillar-copy"><span class="kicker">STRATEGIC OBJECTIVES</span><h2>Seven priorities for the years ahead.</h2>' . vp_chips($objectives['items']) . vp_details($objectives, 'What each priority means') . '</div></section>';
    if ($develop['notes']) $html .= '<section class="sv-banner vp-banner-text"><img src="assets/sustainability/mpwapwa-landscape-1600.webp" alt="Green plains and hills in Mpwapwa District, Dodoma Region" width="1600" height="1067" loading="lazy" decoding="async"><div class="sv-banner-copy"><span class="kicker">HOW WE INTEND TO DEVELOP</span><h2>Evidence guides every decision.</h2>' . vp_paragraphs($develop['notes']) . '</div></section>';
    return $html . $leadership . vp_cta('LET’S TALK', 'Explore where your perspective fits.', 'contact', 'Start a conversation') . '</div>';
}

function server_project_visual(array $page): string
{
    $s = visual_sections($page['body']);
    $empty = ['items' => [], 'notes' => []];
    $process = $s['From feedstock to finished material'] ?? $empty;
    $roadmap = $s['A development roadmap'] ?? $empty;
    $location = $s['Location & Infrastructure'] ?? ($s['Planning for the place'] ?? $empty);
    $hero = vp_image(vp_page_image($page, 'assets/project-site.webp'), 'Mining equipment and workers at the project site', 1280, 1280, true);
    $html = '<div class="sustain-page visual-page" data-layout="project">' . str_replace('<nav class="sv-jump"', '<p class="vp-status vp-status-hero">Development &amp; planning</p><nav class="sv-jump"', vp_hero($page, $hero, 'Project site · Mpwapwa', ['vp-process' => 'Process', 'vp-roadmap' => 'Roadmap', 'vp-location' => 'Location']));
    $html .= '<section class="section vp-block vp-facts" aria-label="Project at a glance">' . vp_tiles([['Location', 'Mpwapwa District, Dodoma Region, Tanzania'], ['Focus', 'Graphite processing and value addition'], ['Current stage', 'Development and planning, with technical evaluation ahead'], ['Proposed products', 'Concentrate, micronised and expandable graphite']]) . '</section>';
    if ($s['']['notes']) $html .= '<section class="section sv-statement"><p>' . html_escape(implode(' ', $s['']['notes'])) . '</p></section>';
    if ($process['items']) $html .= '<section class="sv-banner vp-banner-flow" id="vp-process" aria-labelledby="vp-process-title"><img src="assets/graphite.jpg" alt="Close-up of a graphite mineral specimen" width="1290" height="860" loading="lazy" decoding="async"><div class="sv-banner-copy"><span class="kicker">01 / THE PROPOSED APPROACH</span><h2 id="vp-process-title">From feedstock to finished material.</h2>' . vp_icon_steps($process['items'], 'vp-flow') . vp_details($process, 'How each stage works') . '</div></section>';
    if ($roadmap['items']) $html .= '<section class="section vp-block" id="vp-roadmap"><span class="kicker">02 / THE WAY FORWARD</span><h2>Six decision stages.</h2>' . vp_icon_steps($roadmap['items'], 'vp-timeline') . vp_details($roadmap, 'What happens at each stage') . '</section>';
    if ($location['items'] || $location['notes']) $html .= '<section class="section sv-pillar" id="vp-location"><figure class="sv-pillar-image vp-contain"><img src="assets/mpwapwa-location.svg" alt="Map of Tanzania showing the general Mpwapwa area in Dodoma Region, rather than an exact project site" width="600" height="480" loading="lazy"></figure><div class="sv-pillar-copy"><span class="kicker">03 / MPWAPWA, TANZANIA</span><h2>Location &amp; infrastructure.</h2>' . vp_chips($location['items']) . vp_details($location, 'What we will assess') . '<a class="text-link" href="https://www.openstreetmap.org/search?query=Mpwapwa%2C%20Dodoma%2C%20Tanzania" rel="noopener" target="_blank">Explore the area on a map <span>↗</span></a></div></section>';
    return $html . vp_cta('LET’S TALK ABOUT MPWAPWA', 'Take the conversation further.', 'contact?category=General&topic=project-information', 'Request project information') . '</div>';
}

function server_products_visual(array $page): string
{
    $s = visual_sections($page['body']);
    $empty = ['items' => [], 'notes' => []];
    $hero = vp_image(vp_page_image($page, 'assets/graphite.jpg'), 'Graphite mineral specimen', 1290, 860, true);
    $products = [
        ['Graphite concentrate', '01 / CONCENTRATE', 'Graphite-rich material for further processing and high-temperature industrial uses.', ['Foundries', 'Refractories'], 'graphite-concentrate', '<img src="assets/products/graphite-mineral.jpg" alt="Illustrative graphite mineral specimen, rather than a company concentrate sample" width="360" height="353" loading="lazy" decoding="async">', true],
        ['Micronised graphite', '02 / MICRONISED', 'Fine graphite particles for lubricants, friction materials and industrial formulations.', ['Lubricants', 'Friction materials'], 'micronised-graphite', '<img src="assets/products/lubrication.jpg" alt="Industrial gears illustrating potential lubrication applications for micronised graphite" width="1000" height="1500" loading="lazy" decoding="async">', false],
        ['Expandable graphite', '03 / EXPANDABLE', 'A treated graphite for protective coatings and fire-protection formulations.', ['Protective coatings', 'Fire protection'], 'expandable-graphite', '<img src="assets/products/expandable-graphite.jpg" alt="Illustrative comparison of unexpanded graphite on the left and expanded graphite on the right" width="1596" height="1666" loading="lazy" decoding="async">', true],
    ];
    $html = '<div class="sustain-page visual-page" data-layout="products">' . str_replace('<nav class="sv-jump"', '<p class="vp-status vp-status-hero">Proposed products · under evaluation</p><nav class="sv-jump"', vp_hero($page, $hero, '', ['vp-products' => 'Products', 'vp-applications' => 'Applications', 'vp-requirements' => 'Your requirements']));
    if ($s['']['notes']) $html .= '<section class="section sv-statement"><p>' . html_escape(implode(' ', $s['']['notes'])) . '</p></section>';
    $html .= '<div id="vp-products">';
    foreach ($products as $n => [$name, $kicker, $line, $tags, $topic, $image, $contain]) {
        $section = $s[$name] ?? $empty;
        $html .= '<section class="section sv-pillar' . ($n % 2 ? ' sv-reverse' : '') . '" id="' . $topic . '"><figure class="sv-pillar-image' . ($contain ? ' vp-contain' : '') . '">' . $image . '</figure><div class="sv-pillar-copy"><span class="kicker">' . $kicker . '</span><h2>' . html_escape($name) . '</h2><p class="vp-lead-note">' . html_escape($line) . '</p><div class="vp-tags">' . implode('', array_map(fn($t) => '<span>' . html_escape($t) . '</span>', $tags)) . '</div>' . vp_details($section, 'Uses & requirements') . '<a class="text-link" href="contact?category=Products&amp;topic=' . $topic . '">Discuss this product <span>↗</span></a></div></section>';
    }
    $html .= '</div>';
    $apps = $s['Where graphite can make a difference'] ?? $empty;
    $images = ['Steel & Foundries' => ['foundry.jpg', 'Workers pouring molten metal in a foundry', 'graphite-concentrate'], 'Friction Materials' => ['brakes.jpg', 'Vehicle disc brake and caliper', 'micronised-graphite'], 'Industrial Lubricants' => ['lubrication.jpg', 'Industrial machinery that requires lubrication', 'micronised-graphite'], 'Energy Storage' => ['energy-storage.jpg', 'Electric-vehicle charging', 'battery-materials']];
    $cards = '';
    foreach ($apps['items'] as [$title, $text]) {
        [$file, $alt, $topic] = $images[$title] ?? ['construction.jpg', $title, 'graphite-concentrate'];
        $cards .= '<article class="vp-news-card"><a href="contact?category=Products&amp;topic=' . $topic . '"><div class="vp-news-image"><img src="assets/products/' . $file . '" alt="' . html_escape($alt) . '" loading="lazy" decoding="async"></div><div class="vp-news-copy"><h3>' . html_escape($title) . '</h3><p>' . html_escape($text) . '</p><span>Discuss this application ↗</span></div></a></article>';
    }
    $cards .= '<article class="vp-news-card"><a href="contact?category=Products&amp;topic=expandable-graphite"><div class="vp-news-image"><img src="assets/products/construction.jpg" alt="Building construction, where protective coating systems may be used" loading="lazy" decoding="async"></div><div class="vp-news-copy"><h3>Fire Protection</h3><p>Expandable graphite can form part of protective coating formulations that swell when heated.</p><span>Discuss this application ↗</span></div></a></article>';
    $html .= '<section class="section vp-block" id="vp-applications"><span class="kicker">FROM MATERIAL TO APPLICATION</span><h2>Where graphite can make a difference.</h2>' . ($apps['notes'] ? '<p class="vp-lead-note">' . html_escape($apps['notes'][0]) . '</p>' : '') . '<div class="vp-news-grid vp-app-grid">' . $cards . '</div></section>';
    $req = $s['Start with your requirements'] ?? $empty;
    $needs = ['Application', 'Carbon and impurities', 'Particle or flake size', 'Indicative demand', 'Packaging', 'Destination', 'Qualification'];
    $html .= '<section class="sv-banner vp-banner-flow" id="vp-requirements" aria-labelledby="vp-req-title"><img src="assets/products/foundry.jpg" alt="Workers pouring molten metal in a foundry" width="1200" height="800" loading="lazy" decoding="async"><div class="sv-banner-copy"><span class="kicker">START WITH YOUR APPLICATION</span><h2 id="vp-req-title">What does your industry need?</h2>' . vp_icon_steps(array_map(fn($t) => [$t, ''], $needs), 'vp-flow vp-flow-compact') . vp_paragraphs($req['notes']) . '<a class="btn" href="contact?category=Products">Discuss your requirements <span aria-hidden="true">↗</span></a></div></section>';
    return $html . '</div>';
}
