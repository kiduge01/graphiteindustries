<?php
declare(strict_types=1);
// Builds the public-site stylesheet and script bundles in public/assets/build/ and
// updates the references in public/index.html. Run after editing any public CSS or JS:
//   php bin/build-assets.php
// Source files stay in public/assets/ and remain the files to edit. CSS rules whose
// class names appear nowhere in the templates or scripts are dropped; the check is
// deliberately generous (any matching word in the sources keeps a rule).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$assets = $root . '/public/assets';
$bundles = [
    // Loaded on every page, in this order. improvements.css stays separate because the
    // homepage deliberately does not load it (see the media toggle in public/index.php).
    'site-base.css' => ['style.css', 'premium.css', 'frame.css', 'navigation.css'],
    'site-refinements.css' => ['improvements.css'],
    'site-pages.css' => ['about-sample-a.css', 'leadership-spotlight.css', 'sustainability-page.css', 'corporate.css', 'brand.css'],
    'site.js' => ['core.js', 'leadership-spotlight.js', 'site.js'],
];

// Every word in the templates and scripts that could be a class or id name.
$sources = array_merge(glob($root . '/app/*.php'), glob($root . '/app/*.html'), [$root . '/public/index.html', $root . '/public/index.php']);
foreach ($bundles['site.js'] as $file) $sources[] = $assets . '/' . $file;
$words = [];
foreach ($sources as $file) {
    preg_match_all('/[A-Za-z_][A-Za-z0-9_-]*/', file_get_contents($file), $m);
    foreach ($m[0] as $word) $words[$word] = true;
}

function strip_comments(string $css): string
{
    return preg_replace('~/\*.*?\*/~s', '', $css);
}

// Splits CSS into top-level blocks: [prelude, body] pairs, with body null for @import etc.
function css_blocks(string $css): array
{
    $blocks = []; $depth = 0; $start = 0; $prelude = null; $len = strlen($css); $quote = '';
    for ($i = 0; $i < $len; $i++) {
        $c = $css[$i];
        if ($quote !== '') { if ($c === $quote && $css[$i - 1] !== '\\') $quote = ''; continue; }
        if ($c === '"' || $c === "'") { $quote = $c; continue; }
        if ($c === '{') { if ($depth === 0) { $prelude = trim(substr($css, $start, $i - $start)); $start = $i + 1; } $depth++; }
        elseif ($c === '}') { $depth--; if ($depth === 0) { $blocks[] = [$prelude, substr($css, $start, $i - $start)]; $start = $i + 1; } }
        elseif ($c === ';' && $depth === 0) { $blocks[] = [trim(substr($css, $start, $i - $start)), null]; $start = $i + 1; }
    }
    return $blocks;
}

function split_selectors(string $list): array
{
    $parts = []; $depth = 0; $current = '';
    foreach (str_split($list) as $c) {
        if ($c === '(' || $c === '[') $depth++;
        if ($c === ')' || $c === ']') $depth--;
        if ($c === ',' && $depth === 0) { $parts[] = trim($current); $current = ''; continue; }
        $current .= $c;
    }
    $parts[] = trim($current);
    return array_values(array_filter($parts, fn($p) => $p !== ''));
}

function selector_used(string $selector, array $words): bool
{
    // Ignore names inside :not(...), which never require an element to exist.
    $check = preg_replace('/:not\([^)]*\)/', '', $selector);
    preg_match_all('/[.#]([A-Za-z_][A-Za-z0-9_-]*)/', $check, $m);
    foreach ($m[1] as $name) if (!isset($words[$name])) return false;
    return true;
}

function purge(string $css, array $words, array &$stats): string
{
    $out = '';
    foreach (css_blocks($css) as [$prelude, $body]) {
        if ($body === null) { $out .= $prelude . ';'; continue; }
        if (str_starts_with($prelude, '@media') || str_starts_with($prelude, '@supports') || str_starts_with($prelude, '@layer')) {
            $inner = purge($body, $words, $stats);
            if (trim($inner) !== '') $out .= $prelude . '{' . $inner . '}';
            continue;
        }
        if (str_starts_with($prelude, '@')) { $out .= $prelude . '{' . $body . '}'; continue; }
        $kept = array_filter(split_selectors($prelude), fn($s) => selector_used($s, $words));
        $stats['rules']++;
        if (!$kept) { $stats['dropped']++; continue; }
        $out .= implode(',', $kept) . '{' . trim($body) . '}';
    }
    return $out;
}

function minify_css(string $css): string
{
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{};,>])\s*/', '$1', $css);
    return str_replace(';}', '}', trim($css));
}

@mkdir($assets . '/build', 0755, true);
foreach (glob($assets . '/build/*') as $old) unlink($old);
$html = file_get_contents($root . '/public/index.html');
foreach ($bundles as $name => $files) {
    $content = str_ends_with($name, '.js') ? "'use strict';" : '';
    $before = 0;
    foreach ($files as $file) {
        $text = file_get_contents($assets . '/' . $file);
        $before += strlen($text);
        $content .= str_ends_with($name, '.js') ? "\n;// " . $file . "\n" . $text : strip_comments($text) . "\n";
    }
    $stats = ['rules' => 0, 'dropped' => 0];
    if (str_ends_with($name, '.css')) $content = minify_css(purge($content, $words, $stats));
    $hash = substr(hash('sha256', $content), 0, 10);
    $file = preg_replace('/\.(css|js)$/', '.' . $hash . '.$1', $name);
    file_put_contents($assets . '/build/' . $file, $content);
    $html = preg_replace('~assets/build/' . preg_quote(preg_replace('/\.(css|js)$/', '', $name), '~') . '\.[a-f0-9]{10}\.' . pathinfo($name, PATHINFO_EXTENSION) . '~', 'assets/build/' . $file, $html);
    printf("%-22s %7d -> %7d bytes%s\n", $file, $before, strlen($content), $stats['rules'] ? sprintf(' (%d of %d rules unused, dropped)', $stats['dropped'], $stats['rules']) : '');
}
file_put_contents($root . '/public/index.html', $html);
