<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Deep Diagnostic V2</h1>";
echo "Current Dir: " . __DIR__ . "<br>";
echo "User: " . get_current_user() . " (UID: " . getmyuid() . ")<br>";

$filesToCheck = [
    'source/function/cache/cache_style.php',
    'config/config_global.php',
    'data/cache/style_1_common.css'
];

echo "<h3>File Status:</h3><ul>";
foreach ($filesToCheck as $f) {
    if (file_exists($f)) {
        echo "<li style='color:green'>Found: $f (Size: " . filesize($f) . " bytes)</li>";
    } else {
        echo "<li style='color:red; font-weight:bold;'>MISSING: $f</li>";
    }
}
echo "</ul>";

echo "<h3>Permission Test:</h3>";
$testDir = './data/cache/';
$testFile = $testDir . 'test_write.txt';
if (file_put_contents($testFile, 'test')) {
    echo "<b style='color:green'>Top-level Write OK</b> (Created $testFile)<br>";
    unlink($testFile);
} else {
    echo "<b style='color:red'>Top-level Write FAILED</b> - Cannot write to $testDir<br>";
    echo "Check ownership: <code>chown -R www-data:www-data " . __DIR__ . "</code><br>";
}

echo "<h3>Config Check:</h3>";
if (file_exists('config/config_global.php')) {
    $cfg = file_get_contents('config/config_global.php');
    if (strpos($cfg, "['cssjscompress'] = 0") !== false) {
        echo "<span style='color:green'>Compress = 0 (Correct)</span><br>";
    } else {
        echo "<span style='color:red'>Compress != 0 (Might be causing issues)</span><br>";
    }
}
?>