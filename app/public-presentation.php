<?php
declare(strict_types=1);

// Server-rendered public pages. JavaScript only adds forms, consent and navigation behaviour.
function server_cover(array $page): string
{
    $image = $page['image'] ?? '';
    if (!preg_match('~^(assets/|uploads/|https://)~', $image)) $image = '';
    $projectSite = ($page['id'] ?? '') === 'project' && $image === 'assets/project-site.webp';
    $actions = ($page['id'] ?? '') === 'project' ? '<p class="project-status"><span aria-hidden="true"></span>Development &amp; planning</p><a class="btn green" href="contact?category=General&amp;topic=mpwapwa-project">Discuss the Project <span aria-hidden="true">↗</span></a>' : '';
    $html = '<section class="page-cover' . ($image ? ' has-image' : '') . ($projectSite ? ' project-site-cover' : '') . '"><div class="cover-copy"><a class="breadcrumb" href="./">Home /</a><span class="kicker">' . html_escape($page['eyebrow'] ?? '') . '</span><h1>' . html_escape($page['title']) . '</h1><p class="lead">' . html_escape($page['summary']) . '</p>' . $actions . '</div>';
    if ($image) {
        $mineral = str_contains($image, 'graphite');
        $alt = $projectSite ? 'Mining equipment and workers at the project site' : ($mineral ? 'Graphite mineral specimen' : 'Representative industrial context, not the Mpwapwa site');
        $responsive = $projectSite ? ' srcset="assets/project-site-640.webp 640w, assets/project-site.webp 1280w" sizes="(max-width:1000px) 100vw, 50vw"' : '';
        $html .= '<figure class="cover-image"><img src="' . html_escape($image) . '"' . $responsive . ' alt="' . $alt . '" width="' . ($projectSite ? '1280' : '1000') . '" height="' . ($projectSite ? '1280' : '750') . '" fetchpriority="high"></figure>';
    }
    return $html . '</section>';
}

function server_home(array $p): string
{
    $image = $p['image'] ?? '';
    if (!preg_match('~^(assets/|uploads/|https://)~', $image)) $image = '';
    ob_start(); ?>
    <section class="hero quiet-hero"><div class="hero-image" style="background-image:url('<?= html_escape($image) ?>')"></div><div class="hero-content"><span class="kicker">TANZANIA / GRAPHITE INDUSTRIES LIMITED</span><div class="green-rule"></div><h1><?= html_escape($p['title']) ?></h1><p><?= html_escape($p['summary']) ?></p><a href="project" class="btn green">Discover Mpwapwa <span>↗</span></a></div><div class="hero-bottom">MPWAPWA, DODOMA <span>EVALUATION &amp; PLANNING</span></div></section>
    <section class="section home-introduction"><span class="kicker">MINERAL POTENTIAL. INDUSTRIAL AMBITION.</span><div><h2>A new chapter<br>for graphite in Tanzania.</h2><a href="about" class="text-link">Meet Graphite Industries <span>↗</span></a></div></section>
    <section class="explore-grid" aria-label="Explore Graphite Industries">
      <a href="project" class="explore-tile project-tile"><img src="assets/logistics.jpg" alt="Illustrative infrastructure and logistics" loading="lazy"><span class="tile-top">01 / THE PROJECT <span aria-hidden="true">↗</span></span><div><h2>Rooted in Mpwapwa.</h2><span class="tile-action">Explore our proposed development</span></div></a>
      <a href="products" class="explore-tile mineral-tile"><img src="assets/graphite.jpg" alt="Graphite mineral" loading="lazy"><span class="tile-top">02 / POTENTIAL PRODUCTS <span aria-hidden="true">↗</span></span><div><h2>Material.<br>Possibility.</h2><span class="tile-action">Explore the product pathways</span></div></a>
      <a href="sustainability" class="explore-tile responsibility-tile"><span class="tile-top">03 / OUR RESPONSIBILITIES <span aria-hidden="true">↗</span></span><div><h2>Progress,<br>considered.</h2><span class="tile-action">Explore our approach</span></div></a>
    </section>
    <?php return ob_get_clean();
}

// Each designed page has its own renderer; any other published CMS page gets a
// plain cover, its sections and a contact prompt.
function server_editorial(array $page, string $id, array $settings): string
{
    $designed = [
        'about' => fn() => server_about_visual($page),
        'project' => fn() => server_project_visual($page),
        'products' => fn() => server_products_visual($page),
        'sustainability' => fn() => server_sustainability($page),
        'investors' => fn() => server_investors($page, $settings),
        'contact' => fn() => server_contact($page, $settings),
        'careers' => fn() => server_careers($page),
    ];
    if (isset($designed[$id])) return $designed[$id]();
    $html = server_cover($page);
    $sections = preg_split('/^## /m', $page['body']);
    $intro = array_shift($sections);
    if (trim($intro) !== '') $html .= '<section class="section"><div class="editorial-body">' . body_paragraphs($intro) . '</div></section>';
    foreach ($sections as $n => $section) {
        [$title, $body] = array_pad(explode("\n", $section, 2), 2, '');
        $html .= '<section class="section editorial-section"><div class="editorial-heading"><span class="section-number">' . sprintf('%02d', $n + 1) . '</span><h2>' . html_escape(trim($title)) . '</h2></div><div class="editorial-body">' . body_paragraphs($body) . '</div></section>';
    }
    return $html . '<section class="next-chapter"><div><span class="kicker">CONTINUE THE CONVERSATION</span><h2>Talk to our team.</h2></div><a class="chapter-link" href="contact">Contact us ↗</a></section>';
}

function map_link(array $settings): string
{
    $url = $settings['map_url'] ?? '';
    if (!preg_match('~^https://[^\s"\'<>]+$~', $url)) {
        $url = ($settings['address'] ?? '') !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($settings['address']) : '';
    }
    return $url;
}

// Complete footer for every visitor, including the project-updates sign-up.
function server_footer(array $settings, bool $imageCredits = false, bool $home = false): string
{
    $company = $settings['company'] ?? 'Graphite Industries Limited';
    $social = '';
    foreach (['linkedin' => 'LinkedIn', 'x' => 'X', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $key => $label) {
        if (preg_match('~^https://[^\s"\'<>]+$~', $settings[$key] ?? '')) {
            $social .= '<a href="' . html_escape($settings[$key]) . '" rel="noopener me" target="_blank">' . $label . ' ↗</a>';
        }
    }
    $phone = $settings['phone'] ?? '';
    $contact = '<a href="mailto:' . html_escape($settings['email'] ?? '') . '">' . html_escape($settings['email'] ?? '') . '</a>'
        . ($phone !== '' ? '<a href="tel:' . html_escape(str_replace(' ', '', $phone)) . '">' . html_escape($phone) . '</a>' : '')
        . (($settings['address'] ?? '') !== '' ? '<address>' . html_escape($settings['address']) . '</address>' : '')
        . (($settings['office_hours'] ?? '') !== '' ? '<small>' . html_escape($settings['office_hours']) . '</small>' : '')
        . ($social ? '<div class="footer-social" aria-label="Social media">' . $social . '</div>' : '');
    $links = ($home ? '' : '<a class="text-link" href="contact">Contact our team <span>↗</span></a>') . '<a class="text-link" href="privacy">Privacy notice <span>↗</span></a><a class="text-link" href="terms">Website terms <span>↗</span></a>'
        . ($imageCredits ? '<a href="image-credits.html">Image credits ↗</a>' : '');
    $registration = ($settings['registration'] ?? '') !== '' ? ' · ' . html_escape($settings['registration']) : '';
    $updates = '<form class="updates-form" id="updates-form"><label for="updates-email"><strong>Project updates</strong><span>Receive verified company announcements by email.</span></label><div class="updates-row"><input id="updates-email" name="email" type="email" required maxlength="190" autocomplete="email" placeholder="Your email address"><button class="btn green" type="submit">Subscribe</button></div><label class="honey" aria-hidden="true">Leave empty<input name="website" tabindex="-1" autocomplete="off"></label><label class="check"><input name="consent" type="checkbox" required><span>I agree to the <a href="privacy">privacy notice</a>. Unsubscribe at any time by email.</span></label><p class="form-result" role="status" aria-live="polite"></p></form>';
    $tagline = $home ? 'Advancing Graphite. Creating Industrial Value.' : ($settings['tagline'] ?? '');
    $top = $home
        ? '<div class="footer-top"><h2>Every partnership<br>starts with a conversation.</h2><a class="btn green" href="contact">Start a conversation <span>↗</span></a></div>'
        : '<div class="footer-top"><strong>' . html_escape($company) . '</strong><span>' . html_escape($settings['tagline'] ?? '') . '</span></div>';
    $intro = $home
        ? '<div><strong>GRAPHITE INDUSTRIES LIMITED</strong><p>Mpwapwa project · Tanzania</p><small>Proposed development. Subject to technical evaluation,<br>applicable approvals and financing.</small></div>'
        : '<div><p>Mpwapwa project · Tanzania</p><small>Proposed development, subject to technical evaluation, applicable approvals and financing.</small></div>';
    return $top . '<div class="footer-grid">' . $intro . '<div>' . $contact . '</div><div>' . $links . '</div></div>' . $updates
        . '<div class="copyright">© ' . gmdate('Y') . ' ' . html_escape($company) . $registration . ' <span>' . html_escape($tagline) . '</span></div>';
}

// Main navigation in the order visitors see it; unpublished pages are left out.
function server_navigation(array $pages, string $current): string
{
    $published = array_column($pages, 'id');
    $items = ['home' => 'Home', 'about' => 'About Us', 'project' => 'Our Project', 'products' => 'Products & Applications', 'sustainability' => 'Sustainability', 'investors' => 'Investors & Partners', 'news' => 'News & Updates', 'careers' => 'Careers', 'contact' => 'Contact Us'];
    $html = '';
    foreach ($items as $id => $label) {
        if ($id !== 'news' && !in_array($id, $published, true)) continue;
        $html .= '<a ' . ($id === $current ? 'class="active" aria-current="page" ' : '') . 'href="' . ($id === 'home' ? './' : $id) . '">' . html_escape($label) . '<span>↗</span></a>';
    }
    return $html;
}

// Title block used by the privacy notice, website terms and unavailable pages.
function server_title_block(string $eyebrow, string $title, string $summary): string
{
    return '<section class="page-heading"><span class="kicker">' . html_escape($eyebrow) . '</span><h1>' . html_escape($title) . '</h1><p class="lead">' . html_escape($summary) . '</p></section>';
}

function company_profile_section(array $settings): string
{
    $file = $settings['company_profile'] ?? '';
    if (!preg_match('~^(assets|uploads)/[a-zA-Z0-9._/-]+\.pdf$~', $file) || str_contains($file, '..')) return '';
    return '<section class="section company-profile"><div><span class="kicker">COMPANY DOCUMENTS</span><h2>Company profile</h2><p>An approved overview of Graphite Industries Limited for investors and partners.</p></div><a class="btn green download" href="' . html_escape($file) . '" download>Download company profile (PDF) <span aria-hidden="true">↓</span></a></section>';
}


// Sustainability: CMS "## Section" blocks with "- Title — description" items become
// icon chips (titles only) plus a disclosure holding the full descriptions.
function sustainability_sections(string $body): array
{
    $parts = preg_split('/^## /m', $body);
    $sections = ['' => ['items' => [], 'notes' => [trim(array_shift($parts))]]];
    foreach ($parts as $part) {
        [$title, $text] = array_pad(explode("\n", $part, 2), 2, '');
        $items = []; $notes = [];
        foreach (preg_split('/\n\s*\n/', trim($text)) as $block) {
            $lines = explode("\n", trim($block));
            if ($lines && count(array_filter($lines, fn($l) => str_starts_with($l, '- '))) === count($lines)) {
                foreach ($lines as $line) $items[] = array_pad(explode(' — ', substr($line, 2), 2), 2, '');
            } elseif (trim($block) !== '') $notes[] = trim($block);
        }
        $sections[trim($title)] = ['items' => $items, 'notes' => $notes];
    }
    return $sections;
}
function sustainability_slug(string $text): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');
}
function server_sustainability(array $page): string
{
    $sections = sustainability_sections($page['body']);
    $chips = fn(array $s) => implode('', array_map(fn($i) => '<li data-icon="' . sustainability_slug($i[0]) . '">' . html_escape($i[0]) . '</li>', $s['items']));
    $details = fn(array $s) => '<dl>' . implode('', array_map(fn($i) => '<dt>' . html_escape($i[0]) . '</dt><dd>' . html_escape($i[1]) . '</dd>', $s['items'])) . '</dl>' . implode('', array_map(fn($n) => '<p>' . html_escape($n) . '</p>', $s['notes']));
    $empty = ['items' => [], 'notes' => []];
    $environment = $sections['Environment'] ?? $empty;
    $people = $sections['People & communities'] ?? $empty;
    $governance = $sections['Governance'] ?? $empty;
    $requirements = $sections['Following Tanzanian requirements'] ?? $empty;
    $additional = '';
    foreach ($sections as $title => $section) {
        if (in_array($title, ['', 'Environment', 'People & communities', 'Governance', 'Following Tanzanian requirements'], true)) continue;
        $additional .= '<section class="section editorial-section"><h2>' . html_escape($title) . '</h2><div class="editorial-body">' . $details($section) . '</div></section>';
    }
    $slots = [
        'eyebrow' => html_escape($page['eyebrow'] ?? ''), 'title' => html_escape($page['title']), 'summary' => html_escape($page['summary']),
        'intro' => html_escape(implode(' ', $sections['']['notes'])),
        'environmentChips' => $chips($environment), 'environmentDetails' => $details($environment),
        'peopleChips' => $chips($people), 'peopleDetails' => $details($people),
        'governanceChips' => $chips($governance), 'governanceDetails' => $details($governance),
        'requirements' => implode('', array_map(fn($i) => '<li><h3>' . html_escape($i[0]) . '</h3><p>' . html_escape($i[1]) . '</p></li>', $requirements['items'])),
        'additional' => $additional,
    ];
    $replace = [];
    foreach ($slots as $key => $value) $replace['{{' . $key . '}}'] = $value;
    return strtr(file_get_contents(__DIR__ . '/sustainability-layout.html'), $replace);
}
