<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'sethome');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// Set Forum as Default Homepage
C::t('common_setting')->update('defaultindex', 'forum.php');

// Clear Cache
require_once libfile('function/cache');
updatecache('setting');
updatecache();

echo "<html><body style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h1 style='color:green'>✅ Homepage is now FORUM!</h1>";
echo "<h2>Updated 'defaultindex' to 'forum.php'</h2>";
echo "<h3><a href='index.php'>Go to Homepage</a></h3>";
echo "</body></html>";
?>