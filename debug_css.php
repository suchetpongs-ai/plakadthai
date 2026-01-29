<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 CSS Cache Debugger</h1>";

$baseDir = __DIR__;
$cacheDir = $baseDir . '/data/cache';

echo "<h3>Checking Directory: $cacheDir</h3>";

if (!is_dir($cacheDir)) {
    echo "<b style='color:red'>❌ Directory does not exist!</b><br>";
} else {
    echo "✅ Directory Exists.<br>";
}

// Check Writable
if (is_writable($cacheDir)) {
    echo "✅ Directory is Writable.<br>";
} else {
    echo "<b style='color:red'>❌ Directory is NOT Writable! (Permission Denied)</b><br>";
    echo "Current Script User: " . get_current_user() . " (" . getmyuid() . ")<br>";
    echo "Directory Owner: " . fileowner($cacheDir) . "<br>";
}

// Try Writing
echo "<h3>Testing Write...</h3>";
$testFile = $cacheDir . '/test_perm_check.txt';
if (@file_put_contents($testFile, 'TEST WRITE OK')) {
    echo "<h2 style='color:green'>✅ WRITE SUCCESS! (Permissions are OK)</h2>";
    unlink($testFile); // Clean up
} else {
    echo "<h2 style='color:red'>❌ WRITE FAILED! System cannot create files here.</h2>";
    $err = error_get_last();
    echo "Error Details: " . $err['message'] . "<br>";
}

// Check if Style File exists
echo "<h3>Checking CSS File...</h3>";
$files = glob($cacheDir . '/style_1_common.css');
if ($files) {
    echo "Found CSS File: " . basename($files[0]) . " (Size: " . filesize($files[0]) . " bytes)<br>";
} else {
    echo "<b style='color:orange'>⚠️ No CSS File Found (style_1_common.css)</b><br>";
    echo "This means Discuz failed to generate it, or it was deleted.<br>";
}

echo "<hr><a href='index.php'>Go to Homepage</a>";
?>