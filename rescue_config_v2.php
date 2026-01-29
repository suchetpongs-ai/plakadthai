<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚑 Starting Discuz Config Rescue V2...</h1>";

$baseDir = __DIR__;
$ucConfig = $baseDir . '/uc_server/data/config.inc.php';
$targetFile = $baseDir . '/config/config_global.php';

// Check UC Config
if (!file_exists($ucConfig)) {
    die("<h2 style='color:red;'>❌ CRITICAL: UC Config not found at $ucConfig</h2>");
}

$ucContent = file_get_contents($ucConfig);

// Extract Credentials
preg_match("/define\('UC_DBHOST',\s*'([^']+)'\)/", $ucContent, $m_host);
preg_match("/define\('UC_DBUSER',\s*'([^']+)'\)/", $ucContent, $m_user);
preg_match("/define\('UC_DBPW',\s*'([^']+)'\)/", $ucContent, $m_pw);
preg_match("/define\('UC_DBNAME',\s*'([^']+)'\)/", $ucContent, $m_name);
// Try extract Authkey
preg_match("/define\('UC_KEY',\s*'([^']+)'\)/", $ucContent, $m_key);

$host = isset($m_host[1]) ? $m_host[1] : 'localhost';
$user = isset($m_user[1]) ? $m_user[1] : 'root';
$pw = isset($m_pw[1]) ? $m_pw[1] : '';
$name = isset($m_name[1]) ? $m_name[1] : 'discuz';
$authkey = isset($m_key[1]) ? $m_key[1] : 'plakadthai_rescue_' . time();

echo "<ul>";
echo "<li>Target DB: $name</li>";
echo "<li>Target Host: $host</li>";
echo "<li>Config Rescue: <b>Disabling Memory Driver</b></li>";
echo "</ul>";

// Generate Config Content - DISABLE MEMORY and COMPRESSION
$newConfig = "<?php
\$_config = array();

// Database
\$_config['db'][1]['dbhost'] = '$host';
\$_config['db'][1]['dbuser'] = '$user';
\$_config['db'][1]['dbpw'] = '$pw';
\$_config['db'][1]['dbcharset'] = 'utf8mb4';
\$_config['db'][1]['pconnect'] = 0;
\$_config['db'][1]['dbname'] = '$name';
\$_config['db'][1]['tablepre'] = 'pre_';
\$_config['db']['slave'] = array();
\$_config['db']['common']['slave_except_table'] = '';

// Memory - DISABLED TO PREVENT ERRORS
\$_config['memory']['prefix'] = 'discuz_';
\$_config['memory']['driver'] = ''; // Set to EMPTY to disable
\$_config['memory']['redis']['server'] = '';
\$_config['memory']['redis']['port'] = 6379;

// Server
\$_config['server']['id'] = 1;

// Download
\$_config['download']['readmod'] = 2;

// Output
\$_config['output']['charset'] = 'utf-8';
\$_config['output']['forceheader'] = 1;
\$_config['output']['gzip'] = 0;
\$_config['output']['tplrefresh'] = 1;
\$_config['output']['language'] = 'th';
\$_config['output']['staticurl'] = 'static/';
\$_config['output']['ajaxvalidate'] = 0;
\$_config['output']['iecompatible'] = 0;
\$_config['output']['cssjscompress'] = 0; // DISABLED COMPRESSION

// Cookie
\$_config['cookie']['cookiepre'] = 'pkt_';
\$_config['cookie']['cookiedomain'] = '';
\$_config['cookie']['cookiepath'] = '/';

// Security
\$_config['security']['authkey'] = '$authkey';
\$_config['security']['urlxssdefend'] = 1;
\$_config['security']['attackevasive'] = 0;
\$_config['security']['querysafe']['status'] = 1;
\$_config['security']['querysafe']['dfunction']['0'] = 'load_file';
\$_config['security']['querysafe']['dfunction']['1'] = 'hex_long';
\$_config['security']['querysafe']['dfunction']['2'] = 'user_name';
\$_config['security']['querysafe']['daction']['0'] = '@';
\$_config['security']['querysafe']['daction']['1'] = 'intooutfile';
\$_config['security']['querysafe']['daction']['2'] = 'intodumpfile';
\$_config['security']['querysafe']['daction']['3'] = 'unionselect';
\$_config['security']['querysafe']['daction']['4'] = '(select';
\$_config['security']['querysafe']['daction']['5'] = 'unionall';
\$_config['security']['querysafe']['daction']['6'] = 'uniondistinct';
\$_config['security']['querysafe']['daction']['7'] = '@';

\$_config['admincp']['founder'] = '1';
\$_config['admincp']['forcesecure'] = 0;
\$_config['admincp']['checkip'] = 1;
\$_config['admincp']['runquery'] = 0;
\$_config['admincp']['dbimport'] = 1;
?>";

if (file_put_contents($targetFile, $newConfig)) {
    echo "<h1>✅ Config V2 Restored!</h1>";
    echo "<h3>Memory Driver Disabled. Try <a href='index.php'>Homepage</a>.</h3>";
} else {
    echo "<h1 style='color:red'>❌ Write Failed! Check Permissions.</h1>";
}
?>