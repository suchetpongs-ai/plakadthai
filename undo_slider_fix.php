<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'undo_slider_fix');
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

        // Cleanup empty lines left behind (optional but nice)
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

        // Remove CSS blocks
        $cssContent = str_replace([
            "/* FORCE SLIDER HEIGHT CSS */",
            "/* UNIVERSAL FIX CSS */",
            ".slidebox, .slideshow, .slideshow li {",
            "    height: 400px !important;",
            "    border: 5px solid #00FF00 !important;",
            "}",
            ".slidebox img, .slideshow img {",
            "    object-fit: cover !important;",
            "    height: 100% !important;",
            "    width: 100% !important;",
            "}",
            "    height: 450px !important;",
            "    border: 5px solid #0000FF !important; /* Blue Border */"
        ], "", $cssContent);

        // Regex for cleaner block removal might be safer if strings vary slightly
        $cssContent = preg_replace('/\/\* UNIVERSAL FIX CSS \*\/.*?\}/s', '', $cssContent);
        $cssContent = preg_replace('/\/\* FORCE SLIDER HEIGHT CSS \*\/.*?\}/s', '', $cssContent);
        // Also remove the specific chunks we added blindly

        if (strlen($cssContent) != $originalCssSize) {
            file_put_contents($cssPath, $cssContent);
            echo "✅ Cleaned extend_common.css<br>";
        } else {
            echo "ℹ️ extend_common.css appeared clean<br>";
        }
    }
}

// --- 3. DELETE TEMPORARY SCRIPTS ---
echo "<h2>Deleting Repair Scripts</h2>";
$scriptsToDelete = [
    'resize_slider.php',
    'force_js_slider.php',
    'final_slider_fix.php',
    'diag_v2.php' // Optional cleanup
];

foreach ($scriptsToDelete as $script) {
    if (file_exists(DISCUZ_ROOT . './' . $script)) {
        if (unlink(DISCUZ_ROOT . './' . $script)) {
            echo "🗑️ Deleted $script<br>";
        } else {
            echo "⚠️ Could not delete $script (Permission?)<br>";
        }
    }
}

// --- 4. FLUSH CACHE ---
echo "<h2>Step 3: Final Cache Wipe</h2>";
$tplDir = DISCUZ_ROOT . './data/template/';
$files = glob($tplDir . '*.php');
if ($files) {
    foreach ($files as $f)
        if (basename($f) != 'index.php')
            unlink($f);
    echo "✅ Wiped Compiled Templates.<br>";
}
require_once libfile('function/cache');
updatecache();

echo "<h1>✨ RESTORE COMPLETE.</h1>";
echo "<h3>The site should be back to original state. <a href='index.php'>Go Home</a></h3>";
?>