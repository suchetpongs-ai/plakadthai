<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debugging Config File</h1>";

$f = './config/config_global.php';
if (!file_exists($f))
    die("Config missing");

// 1. Show relevant lines (Safe Mode - No Passwords)
$content = file_get_contents($f);
$lines = explode("\n", $content);
echo "<h3>File Content (Last 10 lines & 'cssjscompress' lines):</h3><pre>";
foreach ($lines as $i => $line) {
    // Show line if it mentions compress OR is near the end
    if (strpos($line, 'cssjscompress') !== false || $i > count($lines) - 10) {
        echo ($i + 1) . ": " . htmlspecialchars($line) . "\n";
    }
}
echo "</pre><hr>";

// 2. Test Syntax
echo "<h3>Testing PHP Syntax...</h3>";
try {
    include $f;
    echo "<h2 style='color:green'>Syntax OK! (Include successful)</h2>";
} catch (ParseError $e) {
    echo "<h2 style='color:red'>❌ SYNTAX ERROR: " . $e->getMessage() . "</h2>";
    echo "at Line: " . $e->getLine();
} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ ERROR: " . $e->getMessage() . "</h2>";
}
?>