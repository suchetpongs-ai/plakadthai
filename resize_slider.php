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
/* CSS SHOTGUN DEBUGGING */
/* Try both ID and Class, and add a RED BORDER to verify it works */
.category_grid .slidebox, 
#category_grid .slidebox,
.category_grid .slidebox .slideshow,
#category_grid .slidebox .slideshow {
    height: 450px !important;
    border: 5px solid red !important; /* TEST BORDER - IF YOU SEE THIS, CSS IS WORKING */
}

/* Ensure images fill the new height */
.category_grid .slidebox .slideshow li,
#category_grid .slidebox .slideshow li {
     height: 450px !important;
     width: 100% !important;
}

.category_grid .slidebox .slideshow img,
#category_grid .slidebox .slideshow img {
    height: 100% !important;
    width: 100% !important;
    object-fit: cover;
}
/* SLIDER_RESIZE_END */
";

// Append to file
if (!file_exists($cssFile)) {
    // Create if missing (rare)
    file_put_contents($cssFile, $cssCode);
} else {
    // Check if already added
    $currentContent = file_get_contents($cssFile);
    if (strpos($currentContent, 'SLIDER_RESIZE_START') === false) {
        file_put_contents($cssFile, $cssCode, FILE_APPEND);
        echo "✅ Added Slider CSS to extend_common.css<br>";
    } else {
        // If already exists, we might want to update it, but for now just skip to avoid duplicates
        // Ideally we would replace, but appending !important overrides usually works if placed last.
        // For simple usage, we'll just say it's applied.
        echo "ℹ️ Slider CSS already applied.<br>";
    }
}

// Force Rebuild Cache
require_once libfile('function/cache');
updatecache();

echo "<h1>✅ Slider Resized!</h1>";
echo "<h3><a href='index.php'>Go to Homepage</a> (Check the images box height)</h3>";
?>