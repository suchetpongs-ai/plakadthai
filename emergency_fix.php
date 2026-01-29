<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'emergency');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// 1. ATTEMPT TO DELETE CUSTOM CSS
$files_to_delete = array(
    DISCUZ_ROOT . './template/default/common/extend_common.css',
    DISCUZ_ROOT . './template/discuzinth/common/extend_common.css'
);
foreach ($files_to_delete as $f) {
    if (file_exists($f)) {
        if (@unlink($f)) {
            echo "Deleted: $f <br>";
        } else {
            echo "<b style='color:red'>Failed to delete (Permission Denied): $f</b> - You must delete this manually via SSH!<br>";
        }
    }
}

// 2. HARD CLEAR CACHE
$cache_dirs = array(
    DISCUZ_ROOT . './data/cache/',
    DISCUZ_ROOT . './data/template/',
    DISCUZ_ROOT . './data/sysdata/'
);

foreach ($cache_dirs as $dir) {
    $files = glob($dir . '*');
    foreach ($files as $file) {
        if (is_file($file) && basename($file) != 'index.htm') {
            @unlink($file);
        }
    }
}

// 3. RESET DEFAULT STYLE
C::t('common_setting')->update('styleid', 1); // Force Default
require_once libfile('function/cache');
updatecache();

echo "<hr>";
echo "<h1 style='color:green'>✅ SYSTEM RESET DONE</h1>";
echo "<h3>Styles have been reset to Factory Default. Cache Cleared.</h3>";
echo "<h2><a href='index.php'>Go to Homepage</a></h2>";
?>