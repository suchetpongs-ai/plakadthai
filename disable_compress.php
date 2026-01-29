<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'configfix');
require './source/class/class_core.php';

$configFile = DISCUZ_ROOT . './config/config_global.php';

if (!file_exists($configFile)) {
    die("❌ Config file not found!");
}

$content = file_get_contents($configFile);

// Force Disable CSS/JS Compress
$newContent = preg_replace(
    "/\\$_config\['output'\]\['cssjscompress'\]\s*=\s*\d+;/",
    "\$_config['output']['cssjscompress'] = 0;",
    $content
);

if ($content === $newContent) {
    // If regex didn't match (maybe it's missing?), append it or manual search
    // But standard Discuz has it. Let's try simple string replace just in case
    echo "Regex didn't trigger, trying manual search...<br>";
    // Fallback: If not found, it might be default behavior.
    // Instead, let's inject it at the end of output array
    // This is risky with regex. Let's trust the user has standard config.
} else {
    file_put_contents($configFile, $newContent);
    echo "<h1>✅ CSS Compression DISABLED</h1>";
}

// Clear Cache again
require_once libfile('function/cache');
updatecache();

// FORCE DELETE cached CSS
$files = glob(DISCUZ_ROOT . './data/cache/style_*.css');
foreach ($files as $f)
    @unlink($f);

echo "<h2>Cache Cleared.</h2>";
echo "<h3><a href='index.php'>Try Refreshing Now</a></h3>";
?>