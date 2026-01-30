<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'final_slider_fix');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

echo "<h1>🚀 Final Slider Fix (The 'New File' Strategy)</h1>";

// --- CONFIGURATION ---
$themeDirs = [
    DISCUZ_ROOT . './template/discuzinth/common/',
    DISCUZ_ROOT . './template/default/common/'
];

// --- 1. JS INJECTION ---
$jsCode = "
<!-- FORCE SLIDER HEIGHT JS START -->
<script type=\"text/javascript\">
(function() {
    function forceResize() {
        // SETTINGS
        var targetHeight = '420px';
        var debugBorder = '5px solid #00FF00'; /* GREEN */
        
        var selectors = [
            '.slidebox', 
            '.slideshow', 
            '#category_grid .slidebox',
            '.module-cl li'
        ];
        
        // Resize Containers
        selectors.forEach(function(sel) {
            var elems = document.querySelectorAll(sel);
            for(var i=0; i<elems.length; i++) {
                elems[i].style.setProperty('height', targetHeight, 'important');
                elems[i].style.setProperty('border', debugBorder, 'important');
                elems[i].style.setProperty('box-sizing', 'border-box', 'important');
                elems[i].style.setProperty('overflow', 'hidden', 'important');
            }
        });
        
        // Resize Images
        var imgs = document.querySelectorAll('.slidebox img, .slideshow img');
        for(var i=0; i<imgs.length; i++) {
             imgs[i].style.setProperty('height', '100%', 'important');
             imgs[i].style.setProperty('width', '100%', 'important');
             imgs[i].style.setProperty('object-fit', 'cover', 'important');
        }
    }

    window.addEventListener('DOMContentLoaded', forceResize);
    window.addEventListener('load', forceResize);
    setInterval(forceResize, 500); // Hammer it every 500ms
})();
</script>
<!-- FORCE SLIDER HEIGHT JS END -->
";

echo "<h2>Step 1: Injecting JS into Headers</h2>";
foreach ($themeDirs as $dir) {
    $file = $dir . 'header.htm';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'FORCE SLIDER HEIGHT JS') !== false) {
            $pattern = '/<!-- FORCE SLIDER HEIGHT JS START -->.*<!-- FORCE SLIDER HEIGHT JS END -->/s';
            $content = preg_replace($pattern, trim($jsCode), $content);
            echo "✅ Updated existing JS in " . basename($dir) . "/header.htm<br>";
        } else {
            $content = str_replace('</head>', $jsCode . "\n</head>", $content);
            echo "✅ Injected NEW JS into " . basename($dir) . "/header.htm<br>";
        }
        file_put_contents($file, $content);
    } else {
        echo "⚠️ File not found: " . basename($dir) . "/header.htm<br>";
    }
}

// --- 2. CSS INJECTION ---
echo "<h2>Step 2: Injecting CSS</h2>";
$cssCode = "
/* FINAL FORCE CSS */
.slidebox, .slideshow, .slideshow li {
    height: 420px !important;
    border: 5px solid #00FF00 !important;
}
.slidebox img, .slideshow img {
    height: 100% !important;
    width: 100% !important;
    object-fit: cover !important;
}
";

foreach ($themeDirs as $dir) {
    $file = $dir . 'extend_common.css';
    if (file_exists($dir)) { // Only if dir exists
        file_put_contents($file, $cssCode, FILE_APPEND);
        echo "✅ Appended CSS to " . basename($dir) . "/extend_common.css<br>";
    }
}

// --- 3. CACHE CLEARING ---
echo "<h2>Step 3: CLEARING ALL CACHES (Nuclear Mode)</h2>";

// Delete CSS Cache
$cacheDir = DISCUZ_ROOT . './data/cache/';
$files = glob($cacheDir . 'style_*.css');
if ($files) {
    foreach ($files as $file)
        unlink($file);
    echo "✅ Wiped CSS Cache.<br>";
}

// Delete Compiled Templates
$tplDir = DISCUZ_ROOT . './data/template/';
$tplFiles = glob($tplDir . '*.php');
if ($tplFiles) {
    $count = 0;
    foreach ($tplFiles as $file) {
        if (basename($file) != 'index.php') {
            unlink($file);
            $count++;
        }
    }
    echo "✅ Wiped $count Compiled Template Files.<br>";
}

require_once libfile('function/cache');
updatecache();

echo "<h1>✨ FIX APPLIED! Go check the homepage now.</h1>";
?>