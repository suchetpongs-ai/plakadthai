<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'resize_slider');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

echo "<h1>📏 Resizing Slideshow Height...</h1>";

// Define the CSS to increase height
// --- 1. DEFINE TARGETS (Universal Fix) ---
// We target both the detected theme and the default fallback
$targets = [
    'discuzinth' => DISCUZ_ROOT . './template/discuzinth/common/',
    'default' => DISCUZ_ROOT . './template/default/common/'
];

echo "<h1>🚀 UNIVERSAL SLIDER FIX</h1>";
echo "Applying fix to ALL themes to ensure we hit the right one.<br>";

// --- 2. THE PAYLOADS ---
$jsPayload = "
<!-- UNIVERSAL SLIDER FIX JS START -->
<script type=\"text/javascript\">
(function() {
    console.log('Slider Fix Running...');
    function forceResize() {
        var boxHeight = '450px';
        // BLUE BORDER this time to distinguish from previous attempts
        var debugBorder = '5px solid #0000FF'; 
        
        // Selectors for every possible container
        var selectors = [
            '.slidebox', 
            '.slideshow', 
            '.slideshow li', 
            '#category_grid .slidebox', 
            '.module-cl li'
        ];
        
        selectors.forEach(function(sel) {
            var elems = document.querySelectorAll(sel);
            if(elems.length > 0) console.log('Found ' + elems.length + ' elements for ' + sel);
            for(var i=0; i<elems.length; i++) {
                elems[i].style.setProperty('height', boxHeight, 'important');
                elems[i].style.setProperty('border', debugBorder, 'important');
                elems[i].style.setProperty('box-sizing', 'border-box', 'important');
                elems[i].style.setProperty('overflow', 'hidden', 'important');
            }
        });
        
        var imgs = document.querySelectorAll('.slidebox img, .slideshow img');
        for(var i=0; i<imgs.length; i++) {
             imgs[i].style.setProperty('height', '100%', 'important');
             imgs[i].style.setProperty('width', '100%', 'important');
             imgs[i].style.setProperty('object-fit', 'cover', 'important');
        }
    }
    
    // Aggressive triggers
    window.addEventListener('scroll', forceResize); // Trigger on scroll too
    window.addEventListener('load', forceResize);
    window.addEventListener('DOMContentLoaded', forceResize);
    setInterval(forceResize, 500);
})();
</script>
<!-- UNIVERSAL SLIDER FIX JS END -->
";

$cssPayload = "
/* UNIVERSAL FIX CSS */
.slidebox, .slideshow, .slideshow li {
    height: 450px !important;
    border: 5px solid #0000FF !important; /* Blue Border */
}
.slidebox img, .slideshow img {
    height: 100% !important;
    width: 100% !important;
    object-fit: cover !important;
}
";

// --- 3. EXECUTE INJECTION ---
foreach ($targets as $name => $path) {
    echo "<h2>Targeting Theme: " . strtoupper($name) . "</h2>";

    if (!file_exists($path)) {
        echo "⚠️ Path not found: $path (Skipping)<br>";
        continue;
    }

    // -> Inject Header
    $headerPath = $path . 'header.htm';
    if (file_exists($headerPath)) {
        $content = file_get_contents($headerPath);
        // Clean old
        $content = preg_replace('/<!-- FORCE SLIDER HEIGHT JS START -->.*<!-- FORCE SLIDER HEIGHT JS END -->/s', '', $content);
        $content = preg_replace('/<!-- DIRECT SLIDER FIX START -->.*<!-- DIRECT SLIDER FIX END -->/s', '', $content);
        $content = preg_replace('/<!-- UNIVERSAL SLIDER FIX JS START -->.*<!-- UNIVERSAL SLIDER FIX JS END -->/s', '', $content);

        // Inject new
        $content = str_replace('</head>', $jsPayload . "\n</head>", $content);

        if (file_put_contents($headerPath, $content)) {
            echo "✅ Injected JS into $headerPath<br>";
        } else {
            echo "❌ Failed to write $headerPath<br>";
        }
    } else {
        echo "⚠️ $headerPath does not exist.<br>";
    }

    // -> Inject CSS
    $cssPath = $path . 'extend_common.css';
    if (file_exists($path)) { // Check dir again just in case
        // Create if missing
        if (!file_exists($cssPath))
            file_put_contents($cssPath, "");

        $cssContent = file_get_contents($cssPath);
        if (strpos($cssContent, 'UNIVERSAL FIX CSS') === false) {
            file_put_contents($cssPath, $cssPayload, FILE_APPEND);
            echo "✅ Appended CSS to $cssPath<br>";
        } else {
            echo "ℹ️ CSS already present in $cssPath<br>";
        }
    }
}

// --- 4. VERIFY ON DISK ---
echo "<h2>Step 2: Verification</h2>";
$checkPath = $targets['discuzinth'] . 'header.htm';
if (file_exists($checkPath)) {
    $checkContent = file_get_contents($checkPath);
    if (strpos($checkContent, 'UNIVERSAL SLIDER FIX JS') !== false) {
        echo "<h3 style='color:green'>SUCCESS: Code is physically present on the server disk!</h3>";
    } else {
        echo "<h3 style='color:red'>FAILURE: Code was NOT saved to disk! Check permissions.</h3>";
    }
}

// --- 5. CLEAR CACHE ---
echo "<h2>Step 3: Cache Wipe</h2>";
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

echo "<h1>✨ DONE. Go to Homepage + Ctrl-F5.</h1>";
echo "<h3>Expect a BLUE Border.</h3>";
?>