<?php
// PURE PHP FIX - NO DISCUZ DEPENDENCIES
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Starting Manual Repair...</h1>";

$baseDir = __DIR__;
$configFile = $baseDir . '/config/config_global.php';

if (!file_exists($configFile)) {
    die("❌ Error: Config file not found at $configFile");
}

// 1. EDIT CONFIG FILE
$content = file_get_contents($configFile);

// Check if setting exists
if (preg_match("/'cssjscompress'\]\s*=\s*\d+;/", $content)) {
    echo "Found existing compression setting... <b>Disabling it.</b><br>";
    $newContent = preg_replace("/'cssjscompress'\]\s*=\s*\d+;/", "'cssjscompress'] = 0;", $content);
} else {
    echo "Compress setting not found... <b>Adding it.</b><br>";
    // Look for end of file
    $newContent = str_replace('?>', "\n\$_config['output']['cssjscompress'] = 0;\n?>", $content);
}

// Write back
if ($content !== $newContent) {
    if (file_put_contents($configFile, $newContent)) {
        echo "<h2 style='color:green'>✅ Config Saved Successfully.</h2>";
    } else {
        echo "<h2 style='color:red'>❌ Failed to write config file! Permission Denied.</h2>";
        exit;
    }
} else {
    echo "<h2 style='color:blue'>ℹ️ Config was already correct.</h2>";
}

// 2. CLEAR STYLE CACHE MANUALLY
echo "<h3>Cleaning CSS Cache...</h3>";
$cacheFiles = glob($baseDir . '/data/cache/style_*.css');
$count = 0;
if ($cacheFiles) {
    foreach ($cacheFiles as $f) {
        if (@unlink($f)) {
            echo "Deleted: " . basename($f) . "<br>";
            $count++;
        }
    }
}
echo "<b>Total Cache Files Deleted: $count</b><br>";

echo "<hr><h1>🎉 FIX COMPLETE</h1>";
echo "<h3><a href='index.php'>Go to Homepage</a></h3>";
?>