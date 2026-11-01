<?php
declare(strict_types=1);

// The server always provides readable content and working navigation. JavaScript
// adds enquiry handling and the richer application/partner arrangements.
function server_cover(array $page): string
{
    $image = $page['image'] ?? '';
    if (!preg_match('~^(assets/|uploads/|https://)~', $image)) $image = '';
    $projectSite = ($page['id'] ?? '') === 'project' && $image === 'assets/project-site.webp';
    $actions = ($page['id'] ?? '') === 'project' ? '<p class="project-status"><span aria-hidden="true"></span>Development &amp; planning</p><a class="btn green" href="?page=contact&amp;category=General&amp;topic=mpwapwa-project">Discuss the Project <span aria-hidden="true">↗</span></a>' : '';
    $html = '<section class="page-cover' . ($image ? ' has-image' : '') . ($projectSite ? ' project-site-cover' : '') . '"><div class="cover-copy"><a class="breadcrumb" href="?page=home">Home /</a><span class="kicker">' . html_escape($page['eyebrow'] ?? '') . '</span><h1>' . html_escape($page['title']) . '</h1><p class="lead">' . html_escape($page['summary']) . '</p>' . $actions . '</div>';
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
    <section class="hero quiet-hero"><div class="hero-image" style="background-image:url('<?= html_escape($image) ?>')"></div><div class="hero-content"><span class="kicker">TANZANIA / GRAPHITE INDUSTRIES LIMITED</span><div class="green-rule"></div><h1><?= html_escape($p['title']) ?></h1><p><?= html_escape($p['summary']) ?></p><a href="?page=project" class="btn green">Discover Mpwapwa <span>↗</span></a></div><div class="hero-bottom">MPWAPWA, DODOMA <span>EVALUATION &amp; PLANNING</span></div></section>
    <section class="section home-introduction"><span class="kicker">MINERAL POTENTIAL. INDUSTRIAL AMBITION.</span><div><h2>A new chapter<br>for graphite in Tanzania.</h2><a href="?page=about" class="text-link">Meet Graphite Industries <span>↗</span></a></div></section>
    <section class="explore-grid" aria-label="Explore Graphite Industries">
      <a href="?page=project" class="explore-tile project-tile"><img src="assets/logistics.jpg" alt="Illustrative infrastructure and logistics" loading="lazy"><span class="tile-top">01 / THE PROJECT <span aria-hidden="true">↗</span></span><div><h2>Rooted in Mpwapwa.</h2><span class="tile-action">Explore our proposed development</span></div></a>
      <a href="?page=products" class="explore-tile mineral-tile"><img src="assets/graphite.jpg" alt="Graphite mineral" loading="lazy"><span class="tile-top">02 / POTENTIAL PRODUCTS <span aria-hidden="true">↗</span></span><div><h2>Material.<br>Possibility.</h2><span class="tile-action">Explore the product pathways</span></div></a>
      <a href="?page=sustainability" class="explore-tile responsibility-tile"><span class="tile-top">03 / OUR RESPONSIBILITIES <span aria-hidden="true">↗</span></span><div><h2>Progress,<br>considered.</h2><span class="tile-action">Explore our approach</span></div></a>
    </section>
    <?php return ob_get_clean();
}

// Turn the CMS numbered stages into native disclosures; all copy stays escaped.
function server_project_steps(string $body, string $class): array
{
    $items = []; $copy = [];
    foreach (explode("\n", $body) as $line) {
        if (preg_match('/^\d+\. (.+)$/u', $line, $match)) $items[] = explode(' — ', $match[1], 2);
        else $copy[] = $line;
    }
    $html = '';
    if ($items) {
        $html = '<ol class="project-steps ' . $class . '">';
        foreach ($items as $n => $item) {
            $html .= '<li><details><summary><span class="project-step-number" aria-hidden="true">' . sprintf('%02d', $n + 1) . '</span><span>' . html_escape($item[0]) . '</span></summary><div class="project-step-copy">' . (isset($item[1]) ? body_paragraphs($item[1]) : '<p>Further details will be shared as the project develops.</p>') . '</div></details></li>';
        }
        $html .= '</ol>';
    }
    return [body_paragraphs(implode("\n", $copy)), $html];
}

function server_project(array $page): string
{
    $slots = ['intro' => '', 'processIntro' => '', 'process' => '', 'roadmapIntro' => '', 'roadmap' => '', 'location' => '', 'additional' => ''];
    $sections = preg_split('/^## /m', $page['body']);
    $slots['intro'] = body_paragraphs(array_shift($sections));
    foreach ($sections as $section) {
        [$title, $body] = array_pad(explode("\n", $section, 2), 2, '');
        $title = trim($title);
        if ($title === 'From feedstock to finished material') [$slots['processIntro'], $slots['process']] = server_project_steps($body, 'project-process-steps');
        elseif ($title === 'A development roadmap') [$slots['roadmapIntro'], $slots['roadmap']] = server_project_steps($body, 'project-roadmap-steps');
        elseif (in_array($title, ['Planning for the place', 'Location & Infrastructure'], true)) $slots['location'] = body_paragraphs($body);
        else $slots['additional'] .= '<section class="section editorial-section"><h2>' . html_escape($title) . '</h2><div class="editorial-body">' . body_paragraphs($body) . '</div></section>';
    }
    $replace = [];
    foreach ($slots as $key => $value) $replace['{{' . $key . '}}'] = $value;
    return server_cover($page) . strtr(file_get_contents(__DIR__ . '/project-layout.html'), $replace);
}

function server_products(array $page): string
{
    $slots = ['eyebrow' => html_escape($page['eyebrow'] ?? ''), 'title' => html_escape($page['title']), 'summary' => html_escape($page['summary']), 'intro' => '', 'concentrateDetails' => '', 'micronisedDetails' => '', 'expandableDetails' => '', 'applicationDetails' => '', 'requirements' => '', 'additional' => ''];
    $keys = ['Graphite concentrate' => 'concentrateDetails', 'Micronised graphite' => 'micronisedDetails', 'Expandable graphite' => 'expandableDetails', 'Start with your requirements' => 'requirements'];
    $sections = preg_split('/^## /m', $page['body']);
    $slots['intro'] = body_paragraphs(array_shift($sections));
    foreach ($sections as $section) {
        [$title, $body] = array_pad(explode("\n", $section, 2), 2, '');
        $title = trim($title);
        if (isset($keys[$title])) $slots[$keys[$title]] = body_paragraphs($body);
        elseif ($title === 'Where graphite can make a difference') {
            foreach (preg_split('/^### /m', $body) as $n => $part) {
                if (!$n) $slots['applicationDetails'] .= body_paragraphs($part);
                else {
                    [$heading, $copy] = array_pad(explode("\n", $part, 2), 2, '');
                    $slots['applicationDetails'] .= '<h4>' . html_escape(trim($heading)) . '</h4>' . body_paragraphs($copy);
                }
            }
        } else $slots['additional'] .= '<section class="section editorial-section"><h2>' . html_escape($title) . '</h2><div class="editorial-body">' . body_paragraphs($body) . '</div></section>';
    }
    $replace = [];
    foreach ($slots as $key => $value) $replace['{{' . $key . '}}'] = $value;
    return strtr(file_get_contents(__DIR__ . '/products-layout.html'), $replace);
}

function server_editorial(array $page, string $id, array $settings): string
{
    if ($id === 'about') return file_get_contents(__DIR__ . '/about-sample-a.html');
    if ($id === 'project') return server_project($page);
    if ($id === 'products') return server_products($page);
    $html = server_cover($page);
    $sections = preg_split('/^## /m', $page['body']);
    $intro = array_shift($sections);
    if ($id === 'about' && trim($intro) !== '') {
        $html .= '<section class="section page-introduction"><span class="kicker">OUR APPROACH</span><div>' . body_paragraphs($intro) . '</div></section>';
        $intro = '';
    }
    foreach ($sections as $n => $section) {
        [$title, $body] = array_pad(explode("\n", $section, 2), 2, '');
        $process = $id === 'project' && trim($title) === 'From feedstock to finished material';
        $roadmap = $id === 'project' && trim($title) === 'A development roadmap';
        $copy = body_paragraphs($body);
        if (trim($intro) !== '') { $copy = body_paragraphs($intro) . $copy; $intro = ''; }
        if ($process || $roadmap) $copy = str_replace('<ol class="copy-list">', '<ol class="' . ($process ? 'process-chain' : 'development-roadmap') . '">', $copy);
        $html .= '<section class="section editorial-section ' . ($process ? 'process-section' : ($roadmap ? 'roadmap-section' : '')) . '"><div class="editorial-heading"><span class="section-number">' . sprintf('%02d', $n + 1) . '</span><h2>' . html_escape(trim($title)) . '</h2></div><div class="editorial-body">' . $copy . '</div></section>';
    }
    if ($id !== 'contact' && trim($intro) !== '') $html .= '<section class="section"><div class="editorial-body">' . body_paragraphs($intro) . '</div></section>';
    if ($id === 'contact') {
        $html .= '<section class="section"><h2>Contact our team</h2><p><a class="text-link" href="mailto:' . html_escape($settings['email'] ?? '') . '">' . html_escape($settings['email'] ?? '') . '</a></p><p><a href="tel:' . html_escape(str_replace(' ', '', $settings['phone'] ?? '')) . '">' . html_escape($settings['phone'] ?? '') . '</a></p><p>' . html_escape($settings['address'] ?? '') . '</p><p>Enable JavaScript to use the enquiry form, or contact the team using the details above.</p></section>';
    } else $html .= '<section class="next-chapter"><div><span class="kicker">CONTINUE THE CONVERSATION</span><h2>Talk to our team.</h2></div><a class="chapter-link" href="?page=contact">Contact us ↗</a></section>';
    return $html;
}
