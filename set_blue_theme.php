<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'theme');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

echo "<h1>🎨 Changing Background to Ocean Blue...</h1>";

// 1. Define the CSS (Light Blue with a modern feel)
$cssFile = DISCUZ_ROOT . './template/default/common/extend_common.css';
$cssCode = "
/* OCEAN_THEME_START */
body {
    background: #dcedf6 !important; /* Soft Blue */
    background: linear-gradient(180deg, #dcedf6 0%, #f0f8ff 100%) fixed !important;
}
/* Optional: Make content area pop out */
#wp, .wp {
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}
/* OCEAN_THEME_END */
";

// 2. Append to file
if (!file_exists($cssFile)) {
    // Create if missing
    file_put_contents($cssFile, $cssCode);
} else {
    // Check if already added
    $currentContent = file_get_contents($cssFile);
    if (strpos($currentContent, 'OCEAN_THEME_START') === false) {
        file_put_contents($cssFile, $cssCode, FILE_APPEND);
        echo "✅ Added Blue Theme CSS to extend_common.css<br>";
    } else {
        echo "ℹ️ Ocean Theme already applied (Skipping write).<br>";
    }
}

// 3. Force Rebuild Cache (Use the robust logic from repair tool)
$cacheDirs = ['./data/cache/', './data/template/'];
foreach ($cacheDirs as $dir) {
    if (is_dir(DISCUZ_ROOT . $dir)) {
        $files = glob(DISCUZ_ROOT . $dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && basename($file) != 'index.htm') {
                @unlink($file);
            }
        }
    }
}
require_once libfile('function/cache');
updatecache();

echo "<h1>✅ Theme Updated!</h1>";
echo "<h3><a href='index.php'>Go to Homepage</a> (Should be Blue now!)</h3>";
?>