<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'fixhttps');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// 1. Force HTTPS in Global Settings
$newURL = 'https://plakadthai.com';
C::t('common_setting')->update('siteurl', $newURL);

// 2. Clear System Cache (Correct Function)
require_once libfile('function/cache');
updatecache('setting');
updatecache();

echo "<html><body style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h1 style='color:green'>✅ HTTPS Settings Fixed! (Success)</h1>";
echo "<h2>Site URL updated to: $newURL</h2>";
echo "<h3><a href='$newURL'>Go to Homepage</a> (Should be Secure now)</h3>";
echo "</body></html>";
?>