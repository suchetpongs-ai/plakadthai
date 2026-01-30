<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'resize_slider');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

echo "<h1>📏 Resizing Slideshow Height...</h1>";

// Define the CSS to increase height
// Standard Discuz slidebox is often around 250px. We'll force it to 280px (approx +30px to be safe and noticeable)
// We also adjust the image fit to ensure it looks good.
$cssFile = DISCUZ_ROOT . './template/default/common/extend_common.css';
$cssCode = "
/* SLIDER_RESIZE_START */
/* Increase height of the 4-frame grid slideshow */
/* CSS NUCLEAR OPTION - GLOBAL SELECTORS */
/* Removing parent selectors to ensure we hit the element */
.slidebox, 
.slideshow, 
.slideshow li {
    height: 400px !important; /* Forced Height */
    border: 5px solid #00FF00 !important; /* BRIGHT GREEN BORDER TEST */
    box-sizing: border-box !important;
}

/* Image handling */
.slidebox img, 
.slideshow img {
    height: 100% !important;
    width: 100% !important;
    object-fit: cover !important; 
}

/* Fix caption position if needed */
.slidebox span,
.slideshow span {
    bottom: 0 !important;
}
/* SLIDER_RESIZE_END */
";

// Append to file
if (!file_exists($cssFile)) {
    // Create if missing (rare)
    file_put_contents($cssFile, $cssCode);
    echo "✅ Created extend_common.css with Slider CSS<br>";
} else {
    // Check if already added
    $currentContent = file_get_contents($cssFile);
    if (strpos($currentContent, 'SLIDER_RESIZE_START') === false) {
        file_put_contents($cssFile, $cssCode, FILE_APPEND);
        echo "✅ Added Slider CSS to extend_common.css<br>";
    } else {
        // If it exists, let's FORCE UPDATE it in case we changed the values inside the block
        // We will replace the whole file content to be sure we get the latest CSS
        // (Simplified logic: just append if not found is safer, but here we want to ensure update)
        // For now, let's just write a new block at the end to override previous ones
        file_put_contents($cssFile, $cssCode, FILE_APPEND);
        echo "✅ Appended NEW Slider CSS (Override) to extend_common.css<br>";
    }
}

// FORCE DELETE CACHED CSS FILES
// This is critical because updatecache() sometimes misses generated CSS
$cacheDir = DISCUZ_ROOT . './data/cache/';
$files = glob($cacheDir . 'style_*.css');
if ($files) {
    echo "<h3>🧹 Deleting Cached CSS Files (Force Rebuild):</h3><ul>";
    foreach ($files as $file) {
        if (unlink($file)) {
            echo "<li>Deleted: " . basename($file) . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>⚠️ No cached CSS files found (Disk cache might be empty or using memory cache).</p>";
}

// Force Rebuild Cache System
require_once libfile('function/cache');
updatecache();

echo "<h1>✅ CSS Cache Wiped & Slider Resized!</h1>";
echo "<h3>Current extend_common.css size: " . filesize($cssFile) . " bytes</h3>";
echo "<h3><a href='index.php'>Go to Homepage</a> (The Red Border SHOULD be there now)</h3>";
?>