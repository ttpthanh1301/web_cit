<?php
declare(strict_types=1);

function minify_css(string $css): string
{
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $css);
    // Replace whitespace sequences
    $css = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    // Remove space after colons, before and after brackets
    $css = preg_replace('/ ?([,:;{}]) ?/', '$1', $css);
    $css = str_replace(';}', '}', $css);
    return trim($css);
}

function minify_js(string $js): string
{
    // Remove single line comments (but protect URLs)
    $js = preg_replace('/^(?!\s*https?:\/\/)\s*\/\/.*$/m', '', $js);
    $js = preg_replace('/(?<!:|https|http)\/\/.*$/m', '', $js);
    // Remove multi line comments
    $js = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $js);
    // Replace whitespace sequences
    $js = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $js);
    $js = preg_replace('/\s+/', ' ', $js);
    // Keep spaces around operators. Removing them blindly can corrupt string literals
    // such as IntersectionObserver rootMargin values: "0px 0px -40px 0px".
    return trim($js);
}

$workspace = dirname(__DIR__);

// Minify CSS
$cssPath = $workspace . '/assets/css/app.css';
$minCssPath = $workspace . '/assets/css/app.min.css';
if (is_file($cssPath)) {
    $minified = minify_css(file_get_contents($cssPath));
    file_put_contents($minCssPath, $minified);
    echo "Minified CSS: " . filesize($cssPath) . " bytes -> " . filesize($minCssPath) . " bytes\n";
}

// Minify JS
foreach (['editable', 'gallery', 'navbar', 'counter', 'admin-form-builder'] as $script) {
    $jsPath = $workspace . '/assets/js/' . $script . '.js';
    $minJsPath = $workspace . '/assets/js/' . $script . '.min.js';
    if (is_file($jsPath)) {
        $minified = minify_js(file_get_contents($jsPath));
        file_put_contents($minJsPath, $minified);
        echo "Minified JS {$script}: " . filesize($jsPath) . " bytes -> " . filesize($minJsPath) . " bytes\n";
    }
}

// HTML cache chứa version cũ của CSS/JS, nên cần làm mới sau mỗi lần build.
foreach (glob($workspace . '/cache/pages/*.html') ?: [] as $cachedPage) {
    @unlink($cachedPage);
}
