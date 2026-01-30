<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'resize_slider');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

echo "<h1>📏 Resizing Slideshow Height...</h1>";

// Define the CSS to increase height
echo "<h1>🚀 FINAL ATTEMPT: Direct Template Injection</h1>";
echo "<p>We are bypassing external CSS files and writing directly to the page header.</p>";

// TARGET: Active Theme Header
$themeDir = DISCUZ_ROOT . './template/discuzinth/common/';
$headerFile = $themeDir . 'header.htm';

if (!file_exists($headerFile)) {
    die("❌ Error: header.htm not found at $headerFile");
}

/* 
   STRATEGY:
   Inject a DIRECT <style> block and <script> block into header.htm.
   This overrules ANY external .css file. 
*/

$injectionCode = "
<!-- DIRECT SLIDER FIX START -->
<style type=\"text/css\">
/* FORCE HEIGHT via Inline Style */
.slidebox, .slideshow, .slideshow li, #category_grid .slidebox {
    height: 450px !important;
    min-height: 450px !important;
    border: 5px solid #FF00FF !important; /* MAGENTA BORDER for visibility */
    overflow: hidden !important;
}
.slidebox img, .slideshow img {
    height: 100% !important;
    width: 100% !important;
    object-fit: cover !important;
}
</style>
<script type=\"text/javascript\">
(function() {
    function forceResize() {
        var h = '450px';
        var b = '5px solid #FF00FF';
        var sels = ['.slidebox', '.slideshow', '.slideshow li', '#category_grid .slidebox', '.module-cl li'];
        sels.forEach(function(s) {
            var els = document.querySelectorAll(s);
            for(var i=0; i<els.length; i++) {
                els[i].style.setProperty('height', h, 'important');
                els[i].style.setProperty('border', b, 'important');
            }
        });
    }
    window.addEventListener('load', forceResize);
    setInterval(forceResize, 1000);
})();
</script>
<!-- DIRECT SLIDER FIX END -->
";

$content = file_get_contents($headerFile);

// Remove old injections to keep it clean
$content = preg_replace('/<!-- FORCE SLIDER HEIGHT JS START -->.*<!-- FORCE SLIDER HEIGHT JS END -->/s', '', $content);

// Inject New Code
if (strpos($content, 'DIRECT SLIDER FIX START') !== false) {
    $pattern = '/<!-- DIRECT SLIDER FIX START -->.*<!-- DIRECT SLIDER FIX END -->/s';
    $content = preg_replace($pattern, trim($injectionCode), $content);
    echo "✅ Updated existing Direct Injection in header.htm<br>";
} else {
    $content = str_replace('</head>', $injectionCode . "\n</head>", $content);
    echo "✅ Injected Direct CSS/JS into header.htm<br>";
}

if (file_put_contents($headerFile, $content)) {
    echo "💾 Saved header.htm successfully.<br>";
} else {
    echo "❌ Failed to write header.htm<br>";
}

// CLEAR COMPILED TEMPLATES (Crucial)
$tplDir = DISCUZ_ROOT . './data/template/';
$tplFiles = glob($tplDir . '*.php');
echo "<h3>🧹 Clearing Compiled Templates:</h3>";
if ($tplFiles) {
    foreach ($tplFiles as $file) {
        if (basename($file) != 'index.php')
            unlink($file);
    }
    echo "✅ Deleted compiled templates.<br>";
}

require_once libfile('function/cache');
updatecache();

echo "<h1>✨ DONE. Go to Homepage + Ctrl-F5.</h1>";
echo "<h3>Look for a MAGENTA (Pink/Purple) Border.</h3>";
?>