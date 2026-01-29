<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'admin');
require './source/class/class_core.php';

// Init Core
$discuz = C::app();
$discuz->init();

echo "<h1>🛠️ Checking Style Database...</h1>";

// Check Styles
$styles = DB::fetch_all("SELECT * FROM " . DB::table('common_style'));
echo "<h3>Found " . count($styles) . " styles found in DB:</h3>";

$fixed = false;
foreach ($styles as $s) {
    echo "<li>ID: " . $s['styleid'] . " | Name: " . $s['name'] . " | TemplateID: " . $s['templateid'] . " | Available: <b>" . $s['available'] . "</b> ";

    if ($s['styleid'] == 1 && $s['available'] == 0) {
        DB::update('common_style', ['available' => 1], ['styleid' => 1]);
        echo "<b style='color:green'>-> FIXED: Enabled Style 1.</b>";
        $fixed = true;
    }
    echo "</li>";
}

// Check Templates (Crucial for building cache)
echo "<h3>Checking Templates:</h3>";
$tpls = DB::fetch_all("SELECT * FROM " . DB::table('common_template'));
foreach ($tpls as $t) {
    echo "<li>TPL ID: " . $t['templateid'] . " | Name: " . $t['name'] . " | Directory: " . $t['directory'] . "</li>";
}

echo "<hr>";

if ($fixed) {
    echo "<h3>✅ Enabled Style. Rebuilding Cache...</h3>";
} else {
    echo "<h3>ℹ️ Style was already enabled. Forcing Rebuild...</h3>";
}

// FORCE REBUILD
require_once libfile('function/cache');
updatecache('style');
echo "<b>Cache Rebuild Triggered.</b><br>";

// Check Result
$target = './data/cache/style_1_common.css';
if (file_exists($target)) {
    echo "<h1 style='color:green'>🎉 SUCCESS! CSS File Created!</h1>";
    echo "Size: " . filesize($target) . " bytes<br>";
    echo "<h2><a href='index.php'>Go to Homepage</a></h2>";
} else {
    echo "<h1 style='color:red'>❌ FAILURE! CSS File Still Missing.</h1>";
    echo "Possible Causes: Template table empty, or permissions.";
}
?>