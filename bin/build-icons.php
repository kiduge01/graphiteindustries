<?php
declare(strict_types=1);
// Regenerates the line-icon rules at the end of public/assets/sustainability-page.css.
// Any element with data-icon="<slug>" gets the matching icon as a CSS mask on :before.
// Slugs come from tile titles: lower case, "&" becomes "and", other symbols become "-".
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$paths = [
    'drop' => 'M12 3c3 4 6 7.5 6 11a6 6 0 0 1-12 0c0-3.5 3-7 6-11z',
    'bolt' => 'M13 2 4 14h7l-1 8 9-12h-7z',
    'layers' => 'M12 3 3 8l9 5 9-5zM3 13l9 5 9-5M3 17.5l9 4.5 9-4.5',
    'leaf' => 'M5 19C5 9 11 5 20 4c-1 9-5 15-15 15zM5 19l7-7',
    'helmet' => 'M4 16a8 8 0 0 1 16 0zM3 16h18v3H3zM12 8V5M9 9V6.5M15 9V6.5',
    'chat' => 'M4 5h16v11H9l-5 4zM8 9h8M8 12h5',
    'people' => 'M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM2 20c0-3.3 2.7-6 6-6s6 2.7 6 6M14 14.3c.6-.2 1.3-.3 2-.3 3.3 0 6 2.7 6 6',
    'shield' => 'M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6zM9 12l2 2 4-4',
    'search' => 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5',
    'scale' => 'M12 4v16M6 20h12M5 8h14M7 8l-3 6h6zM17 8l-3 6h6z',
    'doc' => 'M6 3h9l4 4v14H6zM15 3v4h4M9 14l2 2 4-4',
    'factory' => 'M3 21V10l6 4V10l6 4V6l6 4v11zM3 21h18',
    'pin' => 'M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11zM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z',
    'globe' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z',
    'steps' => 'M4 20h4v-6H4zM10 20h4V9h-4zM16 20h4V4h-4z',
    'trend' => 'M3 17l6-6 4 4 8-8M15 7h6v6',
    'cog' => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1',
    'flask' => 'M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3M7.5 15h9',
    'gem' => 'M6 3h12l3 6-9 12L3 9zM3 9h18M12 21 8 9l4-6 4 6z',
    'bag' => 'M5 8h14l-1 13H6zM9 8a3 3 0 0 1 6 0',
    'truck' => 'M2 6h11v10H2zM13 10h5l4 4v2h-9zM6 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM18 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
    'mail' => 'M3 5h18v14H3zM3 6l9 7 9-7',
    'phone' => 'M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z',
    'building' => 'M4 21V4h10v17M14 9h6v12M2 21h20M8 8h2M8 12h2M8 16h2M17 13h1M17 17h1',
    'mountain' => 'M2 20 9 7l4 6 3-4 6 11z',
    'flame' => 'M12 3c1 4 5 5.5 5 10a5 5 0 0 1-10 0c0-2.5 1.5-4 2.5-5 .5 2 1.5 3 2.5 3 0-3-1-5 0-8z',
    'bars' => 'M4 20V10M10 20V4M16 20v-8M22 20H2',
];
$icons = [
    // Sustainability
    'water' => 'drop', 'energy' => 'bolt', 'waste-and-tailings' => 'layers', 'pollution-and-land' => 'leaf',
    'worker-safety' => 'helmet', 'community-engagement' => 'chat', 'local-participation' => 'people',
    'accountability' => 'shield', 'responsible-sourcing' => 'search', 'ethical-conduct' => 'scale', 'compliance-and-reporting' => 'doc',
    // Investors
    'essential-to-industry' => 'factory', 'value-kept-in-tanzania' => 'pin', 'connected-to-markets' => 'globe', 'phased-risk' => 'steps',
    'investment-partners' => 'trend', 'engineering-and-technology' => 'cog', 'laboratories-and-technical-services' => 'flask',
    'feedstock-suppliers' => 'gem', 'potential-buyers' => 'bag', 'logistics-providers' => 'truck',
    // Contact and careers
    'email' => 'mail', 'phone' => 'phone', 'head-office' => 'building', 'project-location' => 'pin',
    'geology' => 'mountain', 'metallurgy' => 'flame', 'process-engineering' => 'cog', 'environment-and-social' => 'leaf',
    'operations' => 'helmet', 'finance-and-administration' => 'bars', 'fair-recruitment' => 'shield',
    // About
    'integrity' => 'shield', 'technical-excellence' => 'cog', 'responsible-development' => 'leaf', 'partnership' => 'people', 'quality' => 'doc',
    'project-evaluation' => 'search', 'feedstock-and-technology' => 'gem', 'product-development' => 'flask', 'partnerships' => 'people',
    'infrastructure' => 'truck', 'responsible-planning' => 'leaf', 'organisation-and-governance' => 'shield',
    // Project
    'location' => 'pin', 'focus' => 'factory', 'current-stage' => 'steps', 'proposed-products' => 'gem',
    'site-and-land' => 'pin', 'utilities-and-services' => 'bolt', 'transport-and-logistics' => 'truck',
    'feedstock-sourcing-and-verification' => 'gem', 'crushing-and-grinding' => 'cog', 'beneficiation-and-flotation' => 'drop',
    'further-purification-where-required' => 'flask', 'product-specific-processing-and-value-addition' => 'factory', 'quality-assurance-packaging-and-delivery' => 'doc',
    'feedstock-verification' => 'search', 'mineralogical-and-metallurgical-testing' => 'flask', 'project-definition-and-pre-feasibility' => 'doc',
    'feasibility-approvals-and-financing' => 'scale', 'implementation-and-commissioning' => 'factory', 'potential-expansion' => 'trend',
    // Products
    'application' => 'factory', 'carbon-and-impurities' => 'flask', 'particle-or-flake-size' => 'layers', 'indicative-demand' => 'bars',
    'packaging' => 'bag', 'destination' => 'globe', 'qualification' => 'doc',
];
$css = "/* Icons: any element with a matching data-icon gets a masked line icon on :before. Generated by bin/build-icons.php. */\n";
// Slugs sharing a shape share one rule, so each SVG is embedded once.
$groups = [];
foreach ($icons as $slug => $icon) $groups[$icon][] = $slug;
foreach ($groups as $icon => $slugs) {
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='1.7' stroke-linecap='round' stroke-linejoin='round'><path d='{$paths[$icon]}'/></svg>";
    $url = 'data:image/svg+xml,' . str_replace(['<', '>', '"', ' '], ['%3C', '%3E', '%22', '%20'], $svg);
    $css .= implode(',', array_map(fn($slug) => "[data-icon=$slug]:before", $slugs)) . "{-webkit-mask-image:url(\"$url\");mask-image:url(\"$url\")}\n";
}
$file = dirname(__DIR__) . '/public/assets/sustainability-page.css';
$current = file_get_contents($file);
$current = preg_replace('~^/\* Icons: any element[^\n]*\n~m', '', $current);
$current = preg_replace('~^\[data-icon=[^\n]*\n~m', '', $current);
file_put_contents($file, rtrim($current) . "\n" . $css);
echo count($icons) . " icons written.\n";
