<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'restore');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// RESTORE CSS TO BASIC (Safety Mode)
$cssFile = DISCUZ_ROOT . './template/default/common/extend_common.css';
$safeCSS = "
/* RESTORED BY AI */
.wp { width: 1180px !important; margin: 0 auto; }
";

if (file_put_contents($cssFile, $safeCSS)) {
    echo "<h1>✅ CSS Restored (Cleaned)</h1>";
} else {
    echo "<h1>❌ Write Failed (Permission Error)</h1>";
}

// FORCE UPDATE CACHE
require_once libfile('function/cache');
updatecache();
$files = glob(DISCUZ_ROOT . './data/cache/style_*.css');
foreach ($files as $f)
    unlink($f);

echo "<h2>Cache Cleared.</h2>";
echo "<h3><a href='index.php'>Go to Homepage</a></h3>";
?>