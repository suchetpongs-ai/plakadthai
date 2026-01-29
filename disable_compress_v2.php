<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'configfix');
require './source/class/class_core.php';

// DEFINITELY INITIALIZE DISCUZ APP THIS TIME
$discuz = C::app();
$discuz->init();

$configFile = DISCUZ_ROOT . './config/config_global.php';

if (!file_exists($configFile)) {
    die("❌ Config file not found!");
}

$content = file_get_contents($configFile);

// Force Disable CSS/JS Compress (Set to 0)
$newContent = preg_replace(
    "/\\$_config\['output'\]\['cssjscompress'\]\s*=\s*\d+;/",
    "\$_config['output']['cssjscompress'] = 0;",
    $content
);

// Fallback if regex fails (append if not present)
if (strpos($newContent, "['cssjscompress'] = 0;") === false) {
    $newContent = str_replace("?>", "\$_config['output']['cssjscompress'] = 0;\n?>", $content);
}

if (file_put_contents($configFile, $newContent)) {
    echo "<h1>✅ Config Updated (Compression OFF)</h1>";
} else {
    echo "<h1>❌ Write Failed</h1>";
}

// Clear Cache properly
require_once libfile('function/cache');
updatecache();

// FORCE DELETE cached CSS
$files = glob(DISCUZ_ROOT . './data/cache/style_*.css');
foreach ($files as $f)
    @unlink($f);

echo "<h2>Cache Cleared Successfully.</h2>";
echo "<h3><a href='index.php'>Go to Homepage</a></h3>";
?>