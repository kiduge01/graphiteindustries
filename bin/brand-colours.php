<?php
declare(strict_types=1);
// Snaps every colour in the public stylesheets onto the corporate palette:
//   neutrals and green-tinted greys -> white / soft grey #F2F3F3 / silver #A7ADB0 / charcoal #25282A
//   greens -> tints and shades of forest green #174A3A
//   golds  -> tints and shades of gold #C6A15B
// Each colour keeps its luminance, so text contrast is preserved; alpha is kept.
// Reds (form errors) are left alone. Run with --dry-run to list changes only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$dry = in_array('--dry-run', $argv, true);
$files = ['style', 'premium', 'frame', 'navigation', 'improvements', 'about-sample-a', 'leadership-spotlight', 'sustainability-page', 'corporate'];

function rgb(string $hex): array { return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))]; }
function lum(array $c): float
{
    $l = array_map(fn($v) => ($v /= 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $c);
    return 0.2126 * $l[0] + 0.7152 * $l[1] + 0.0722 * $l[2];
}
// Colour along a polyline of anchors whose luminance is closest to the target.
function on_ramp(array $anchors, float $target): array
{
    $best = $anchors[0]; $bestDiff = INF;
    for ($i = 0; $i < count($anchors) - 1; $i++) {
        for ($t = 0; $t <= 1.0001; $t += 0.005) {
            $c = array_map(fn($a, $b) => (int)round($a + ($b - $a) * $t), $anchors[$i], $anchors[$i + 1]);
            $d = abs(lum($c) - $target);
            if ($d < $bestDiff) { $bestDiff = $d; $best = $c; }
        }
    }
    return $best;
}
function hsl(array $c): array
{
    [$r, $g, $b] = array_map(fn($v) => $v / 255, $c);
    $max = max($r, $g, $b); $min = min($r, $g, $b); $l = ($max + $min) / 2; $d = $max - $min;
    if ($d == 0) return [0, 0, $l];
    $s = $d / (1 - abs(2 * $l - 1));
    $h = $max == $r ? fmod(($g - $b) / $d, 6) : ($max == $g ? ($b - $r) / $d + 2 : ($r - $g) / $d + 4);
    return [fmod($h * 60 + 360, 360), $s, $l];
}
$white = [255, 255, 255]; $soft = rgb('F2F3F3'); $silver = rgb('A7ADB0'); $charcoal = rgb('25282A');
$green = rgb('174A3A'); $gold = rgb('C6A15B');
$neutral = [$white, $soft, $silver, $charcoal];
$palette = ['ffffff', 'f2f3f3', 'a7adb0', '25282a', '174a3a', 'c6a15b'];

function snap(string $hex): ?string
{
    global $neutral, $white, $charcoal, $green, $gold, $palette;
    if (in_array($hex, $palette, true)) return null;
    $c = rgb($hex); [$h, $s, $l] = hsl($c); $target = lum($c);
    if ($s >= 0.25 && ($h < 20 || $h >= 330)) return null; // reds stay (error states)
    // Tints and shades of green, gold or the neutral scale are already on brand.
    foreach ([[$white, $green, $charcoal], [$white, $gold, $charcoal], $neutral] as $scale) {
        if (array_sum(array_map(fn($a, $b) => abs($a - $b), $c, on_ramp($scale, $target))) <= 6) return null;
    }
    if ($s >= 0.25 && $h >= 25 && $h < 70) $ramp = [$white, $gold, $charcoal];
    elseif ($s >= 0.25 && $h >= 70 && $h < 200 && $l < 0.85) $ramp = [$white, $green, $charcoal];
    else $ramp = $neutral;
    $out = on_ramp($ramp, $target);
    $new = sprintf('%02x%02x%02x', ...$out);
    // Already on a brand scale (within rounding): leave it.
    if (array_sum(array_map(fn($a, $b) => abs($a - $b), $c, $out)) <= 6) return null;
    return $new;
}

$total = 0;
foreach ($files as $name) {
    $path = dirname(__DIR__) . "/public/assets/$name.css";
    $css = file_get_contents($path);
    $changes = 0;
    // Leave colours inside url(...) (embedded SVG icons) untouched.
    $css = preg_replace_callback('~url\([^)]*\)|#([0-9a-fA-F]{8}|[0-9a-fA-F]{6}|[0-9a-fA-F]{3,4})\b~', function ($m) use (&$changes, $dry, $name) {
        if (!isset($m[1])) return $m[0];
        $raw = strtolower($m[1]);
        $full = strlen($raw) <= 4 ? implode('', array_map(fn($ch) => $ch . $ch, str_split($raw))) : $raw;
        $alpha = substr($full, 6);
        $new = snap(substr($full, 0, 6));
        if ($new === null) return $m[0];
        $changes++;
        if ($dry) echo "$name: #$raw -> #$new$alpha\n";
        return '#' . $new . $alpha;
    }, $css);
    if (!$dry) file_put_contents($path, $css);
    $total += $changes;
}
echo ($dry ? 'Would change ' : 'Changed ') . "$total colour values.\n";
