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
echo "<h1>🚀 Forcing Slider Height (JS Injection Strategy)</h1>";

// 1. Identify Target Files (CORRECTED THEME: discuzinth)
$themeDir = DISCUZ_ROOT . './template/discuzinth/common/';
$headerFile = $themeDir . 'header.htm';
$cssFile = $themeDir . 'extend_common.css';

if (!file_exists($themeDir)) {
    die("❌ Error: Theme directory not found at $themeDir. Please verify theme name.");
}

// 2. Inject Javascript into header.htm
echo "<h2>Step 1: Injecting Javascript into header.htm</h2>";
if (file_exists($headerFile)) {
    $content = file_get_contents($headerFile);
    $jsCode = "
<!-- FORCE SLIDER HEIGHT JS START -->
<script type=\"text/javascript\">
(function() {
    function forceResize() {
        var height = '400px';
        var border = '5px solid #00FF00'; /* Green Border */
        // Consolidated selectors list
        var selectors = [
            '.slidebox', 
            '.slideshow', 
            '.slideshow li', 
            '#category_grid .slidebox',
            '.module-cl li'
        ];
        
        selectors.forEach(function(sel) {
            var elems = document.querySelectorAll(sel);
            for(var i=0; i<elems.length; i++) {
                elems[i].style.setProperty('height', height, 'important');
                elems[i].style.setProperty('border', border, 'important');
                elems[i].style.setProperty('box-sizing', 'border-box', 'important');
            }
        });
        
        // Fix images
        var imgs = document.querySelectorAll('.slidebox img, .slideshow img');
        for(var i=0; i<imgs.length; i++) {
             imgs[i].style.setProperty('height', '100%', 'important');
             imgs[i].style.setProperty('width', '100%', 'important');
             imgs[i].style.setProperty('object-fit', 'cover', 'important');
        }
    }

    // Run immediately, on load, and periodically
    window.addEventListener('DOMContentLoaded', forceResize);
    window.addEventListener('load', forceResize);
    setInterval(forceResize, 1000); 
})();
</script>
<!-- FORCE SLIDER HEIGHT JS END -->
";

    // Check if already injected
    if (strpos($content, 'FORCE SLIDER HEIGHT JS') !== false) {
        $pattern = '/<!-- FORCE SLIDER HEIGHT JS START -->.*<!-- FORCE SLIDER HEIGHT JS END -->/s';
        $content = preg_replace($pattern, trim($jsCode), $content);
        echo "✅ Updated existing JS injection in header.htm<br>";
    } else {
        $content = str_replace('</head>', $jsCode . "\n</head>", $content);
        echo "✅ Injected NEW JS block into header.htm<br>";
    }

    if (file_put_contents($headerFile, $content)) {
        echo "💾 Saved header.htm successfully.<br>";
    } else {
        echo "❌ Failed to write to header.htm (Check permissions)<br>";
    }

} else {
    echo "❌ header.htm not found at $headerFile<br>";
}

// 3. Update CSS in the CORRECT theme folder
echo "<h2>Step 2: Updating extend_common.css in 'discuzinth' theme</h2>";
$cssCode = "
/* FORCE SLIDER HEIGHT CSS */
.slidebox, .slideshow, .slideshow li {
    height: 400px !important;
    border: 5px solid #00FF00 !important;
}
.slidebox img, .slideshow img {
    object-fit: cover !important;
    height: 100% !important;
    width: 100% !important;
}
";

// Write CSS
if (file_exists($cssFile)) {
    $currentCss = file_get_contents($cssFile);
    if (strpos($currentCss, 'FORCE SLIDER HEIGHT CSS') === false) {
        file_put_contents($cssFile, $cssCode, FILE_APPEND);
        echo "✅ Appended CSS to extend_common.css<br>";
    } else {
        echo "ℹ️ CSS already exists in extend_common.css<br>";
    }
} else {
    file_put_contents($cssFile, $cssCode);
    echo "✅ Created new extend_common.css<br>";
}

// 4. Clear Cache (THE NUCLEAR OPTION)
echo "<h2>Step 3: Creating Cache Clearing Nuclear Explosion 💥</h2>";

// Clear CSS Cache
$cacheDir = DISCUZ_ROOT . './data/cache/';
$files = glob($cacheDir . 'style_*.css');
if ($files) {
    foreach ($files as $file) {
        unlink($file);
    }
    echo "✅ Deleted all style_*.css cache files.<br>";
}

// Clear COMPILED TEMPLATE Cache (Critical for .htm changes)
$tplDir = DISCUZ_ROOT . './data/template/';
$tplFiles = glob($tplDir . '*.php');
if ($tplFiles) {
    echo "<h3>🧹 Deleting Compiled Templates (Force HTML Rebuild):</h3><ul>";
    $count = 0;
    foreach ($tplFiles as $file) {
        if (basename($file) != 'index.php') { // Keep index.php security file
            unlink($file);
            $count++;
        }
    }
    echo "<li>Deleted $count compiled template files.</li>";
    echo "</ul>";
} else {
    echo "<p>⚠️ No active template cache found.</p>";
}

require_once libfile('function/cache');
updatecache();

echo "<h1>✨ DONE! Go check the homepage.</h1>";
echo "<h3><a href='index.php'>Back to Home</a> (Expect Green Borders!)</h3>";
?>