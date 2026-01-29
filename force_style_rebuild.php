<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'admin');
require './source/class/class_core.php';

// Initialize Diskuz
$discuz = C::app();
$discuz->init();

echo "<h1>🔧 Forcing Style Rebuild...</h1>";

// 1. Force Settings in DB
C::t('common_setting')->update('styleid', 1);
echo "✅ Set Default Style ID = 1 in Database.<br>";

// 2. Clean Old Cache
$files = glob('./data/cache/*.css');
$count = 0;
if ($files) {
    foreach ($files as $f) {
        if (@unlink($f))
            $count++;
    }
}
echo "✅ Cleared $count old CSS cache files.<br>";

// 3. Trigger Cache Rebuild
require_once libfile('function/cache');
updatecache('setting');
updatecache('style');
echo "✅ Triggered Discuz Cache Update System.<br>";

// 4. Verify Result
$target = './data/cache/style_1_common.css';
$tpl = './template/default/common/common.css';

echo "<h3>Inspection Results:</h3>";

if (file_exists($target)) {
    $size = filesize($target);
    echo "<h1 style='color:green'>🎉 SUCCESS! CSS File Created.</h1>";
    echo "File: style_1_common.css (Size: $size bytes)<br>";
    echo "<h3><a href='index.php'>Go to Homepage</a> (Should be fixed!)</h3>";
} else {
    echo "<h1 style='color:red'>❌ FAILURE! CSS File Not Created.</h1>";

    // Diagnose details
    if (!file_exists($tpl)) {
        echo "<b>CRITICAL CAUSE:</b> Source Template Missing ($tpl).<br>";
        echo "You might need to restore the 'template/default' folder.<br>";
    } else {
        echo "Source Template Exists. Detecting File System Issue...<br>";
        if (!is_writable('./data/cache')) {
            echo "<b>CAUSE:</b> Cache Directory Not Writable.<br>";
        } else {
            echo "<b>CAUSE:</b> Unknown build error. Possibly memory limit or PHP error.<br>";
        }
    }
}
?>