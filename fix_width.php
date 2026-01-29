<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'fixwidth');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// 1. Append CSS to extend_common.css
$cssFile = DISCUZ_ROOT . './template/default/common/extend_common.css';
$cssCode = "
/* AI_FIX_WIDTH_START */
.wp { width: 1180px !important; max-width: 98% !important; margin: 0 auto; }
/* AI_FIX_WIDTH_END */
";

// Read current content to check if already exists
$currentContent = file_get_contents($cssFile);
if (strpos($currentContent, 'AI_FIX_WIDTH_START') === false) {
    if (file_put_contents($cssFile, $cssCode, FILE_APPEND)) {
        $msg = "✅ CSS Updated (template/default/common/extend_common.css)";
    } else {
        $msg = "❌ Failed to write CSS file. Permission denied.";
    }
} else {
    $msg = "ℹ️ CSS already applied.";
}

// 2. Clear CSS Cache
$cacheDir = DISCUZ_ROOT . './data/cache/';
$files = glob($cacheDir . 'style_*.css');
$deleted = 0;
if ($files) {
    foreach ($files as $file) {
        if (unlink($file))
            $deleted++;
    }
}

echo "<html><body style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h1>$msg</h1>";
echo "<h2>Cleared $deleted cache files.</h2>";
echo "<h3><a href='index.php'>Go to Homepage</a> (Check if width is 1180px)</h3>";
echo "<p style='color:gray'>Please delete this file after use.</p>";
echo "</body></html>";
?>