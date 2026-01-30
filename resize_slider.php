<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'resize_slider_undo');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

echo "<h1>🔄 Restoring Original Website State</h1>";
echo "<p>Removing all injected Javascript and CSS...</p>";

// --- 1. DEFINE TARGETS ---
$targets = [
    'discuzinth' => DISCUZ_ROOT . './template/discuzinth/common/',
    'default' => DISCUZ_ROOT . './template/default/common/'
];

// --- 2. CLEANUP LOGIC ---
foreach ($targets as $name => $path) {
    echo "<h2>Cleaning Theme: " . strtoupper($name) . "</h2>";

    // -> Clean Header.htm
    $headerPath = $path . 'header.htm';
    if (file_exists($headerPath)) {
        $content = file_get_contents($headerPath);
        $originalSize = strlen($content);

        // Remove known blocks
        $content = preg_replace('/<!-- FORCE SLIDER HEIGHT JS START -->.*<!-- FORCE SLIDER HEIGHT JS END -->/s', '', $content);
        $content = preg_replace('/<!-- DIRECT SLIDER FIX START -->.*<!-- DIRECT SLIDER FIX END -->/s', '', $content);
        $content = preg_replace('/<!-- UNIVERSAL SLIDER FIX JS START -->.*<!-- UNIVERSAL SLIDER FIX JS END -->/s', '', $content);

        // Cleanup empty lines left behind
        $content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);

        if (strlen($content) != $originalSize) {
            if (file_put_contents($headerPath, $content)) {
                echo "✅ Cleaned header.htm (Removed injections)<br>";
            } else {
                echo "❌ Failed to write $headerPath<br>";
            }
        } else {
            echo "ℹ️ header.htm was already clean (or nothing found)<br>";
        }
    }

    // -> Clean extend_common.css
    $cssPath = $path . 'extend_common.css';
    if (file_exists($cssPath)) {
        $cssContent = file_get_contents($cssPath);
        $originalCssSize = strlen($cssContent);

        // Remove CSS blocks via exact string matching or patterns
        // We look for the markers we added
        $cssContent = preg_replace('/\/\* UNIVERSAL FIX CSS \*\/.*?\}/s', '', $cssContent);
        $cssContent = preg_replace('/\/\* FORCE SLIDER HEIGHT CSS \*\/.*?\}/s', '', $cssContent);
        $cssContent = preg_replace('/\/\* FINAL FORCE CSS \*\/.*?\}/s', '', $cssContent);

        // Clean up specific raw css if markers missing
        $cssContent = str_replace(
            ".slidebox, .slideshow, .slideshow li {\r\n    height: 400px !important;\r\n    border: 5px solid #00FF00 !important;\r\n}",
            "",
            $cssContent
        );

        if (strlen($cssContent) != $originalCssSize) {
            file_put_contents($cssPath, $cssContent);
            echo "✅ Cleaned extend_common.css<br>";
        } else {
            echo "ℹ️ extend_common.css appeared clean<br>";
        }
    }
}

// --- 3. FLUSH CACHE (The Nuclear Option) ---
echo "<h2>Step 3: Final Cache Wipe</h2>";
$tplDir = DISCUZ_ROOT . './data/template/';
$files = glob($tplDir . '*.php');
if ($files) {
    foreach ($files as $f) {
        if (basename($f) != 'index.php')
            unlink($f);
    }
    echo "✅ Wiped Compiled Templates.<br>";
}
// Also wipe CSS cache
$cacheDir = DISCUZ_ROOT . './data/cache/';
$files = glob($cacheDir . 'style_*.css');
if ($files) {
    foreach ($files as $f)
        unlink($f);
    echo "✅ Wiped CSS Cache.<br>";
}

require_once libfile('function/cache');
updatecache();

echo "<h1>✨ RESTORE COMPLETE.</h1>";
echo "<h3>The site should be back to normal. <a href='index.php'>Go Home</a></h3>";
?>