<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'sethome');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// Set Portal as Default Homepage
$settings = array(
    'defaultindex' => 'portal.php'
);
foreach ($settings as $key => $val) {
    C::t('common_setting')->update($key, $val);
}

// Clear Cache
require_once libfile('function/cache');
updatecache('setting');
updatecache();

echo "<html><body style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h1 style='color:blue'>✅ Portal is now the Viewer Homepage!</h1>";
echo "<h2>Updated 'defaultindex' to 'portal.php'</h2>";
echo "<h3><a href='index.php'>Go to Homepage</a> (Should land on Portal)</h3>";
echo "</body></html>";
?>