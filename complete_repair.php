<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'repair');
require './source/class/class_core.php';

// Initialize Discuz
$discuz = C::app();
$discuz->init();

echo "<html><head><title>Discuz Repair</title><style>body{font-family:sans-serif;line-height:1.6;padding:20px;max-width:800px;margin:0 auto;background:#f4f4f4;} h1{color:#2c3e50;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .box{background:white;padding:15px;border-radius:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1);margin-bottom:15px;}</style></head><body>";
echo "<h1>🛠️ Discuz Complete Repair Tool</h1>";

// ---------------------------------------------------------
// STEP 1: Restore cache_style.php
// ---------------------------------------------------------
echo "<div class='box'><h3>Step 1: Restoring Critical System Files</h3>";
$cacheStyleFile = DISCUZ_ROOT . './source/function/cache/cache_style.php';
$cacheStyleDir = dirname($cacheStyleFile);

if (!is_dir($cacheStyleDir)) {
    if (mkdir($cacheStyleDir, 0777, true)) {
        echo "Created directory: $cacheStyleDir <br>";
    } else {
        echo "<span class='error'>Failed to create directory: $cacheStyleDir</span><br>";
    }
}

// Content of cache_style.php
$cacheStyleContent = <<<'PHP'
<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_style.php 36311 2016-12-19 01:21:40Z nemohuang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_style() {
	global $_G;
	$data = array();
	$query = DB::query("SELECT s.styleid, s.name, s.templateid, t.directory, t.copyright FROM ".DB::table('common_style')." s
		LEFT JOIN ".DB::table('common_template')." t ON t.templateid=s.templateid
		WHERE s.available='1'");

	while($style = DB::fetch($query)) {
		$data[$style['styleid']] = $style['name'];
		$styleid = $style['styleid'];
		$query_var = DB::query("SELECT * FROM ".DB::table('common_stylevar')." WHERE styleid='$styleid'");
		$stylevar = array();
		while($var = DB::fetch($query_var)) {
			$stylevar[$var['variable']] = $var['substitute'];
		}
		$tpldir = $style['directory'] ? $style['directory'] : './template/default';

		$stylevar['TPLDIR'] = $tpldir;
		$stylevar['STYLEID'] = $styleid;
		$stylevar['TEMPLATEID'] = $style['templateid'];

		$predefinedvars = array('IMGDIR', 'STATICURL', 'BOARDIMG', 'STYLEIMGDIR', 'INPUTBORDER', 'NOTICETEXT', 'HIGHLIGHTLINK', 'COMMONTEXT', 'LIGHTTEXT', 'NOTICETEXT', 'WIDGET_BORDERURL', 'WIDGET_BGCOLOR', 'COMMONBG', 'SPECIALBG', 'SPECIALBORDER', 'TABLEBG', 'HEADERBG', 'FOOTERBG', 'SIDEBG', 'INPUTBG', 'DROPBTNBG', 'FLOATBG', 'FLOATMASKBG', 'MENUCOLOR', 'MENUHOVERCOLOR', 'MENUHOVERTXT', 'WRAPWIDTH', 'WRAPBG', 'WRAPBORDERCOLOR', 'CONTENTWIDTH', 'CONTENTSEPARATION', 'HEADERTEXT', 'FOOTERTEXT', 'SITETITLECOLOR', 'NAVCOLOR', 'NAVTEXT', 'NAVHOVERCOLOR', 'NAVHOVERTEXT', 'LINK', 'HOVER', 'SMALLFONT', 'ANYFONT', 'FONT', 'FONTSIZE', 'SMALLFONTSIZE', 'TABLETEXT', 'MIDTEXT', 'LIGHTTEXT', 'NOTICETEXT', 'MSGTEXT', 'MSGFONTSIZE', 'TITLETEXT', 'LINK', 'HOVER');

		foreach($predefinedvars as $var) {
			if(!isset($stylevar[$var])) {
				$stylevar[$var] = '';
			}
		}

		$stylevar['BRIGHTNESS'] = strpos($stylevar['COMMONBG'], '#') === 0 && (hexdec(substr($stylevar['COMMONBG'], 1, 2)) + hexdec(substr($stylevar['COMMONBG'], 3, 2)) + hexdec(substr($stylevar['COMMONBG'], 5, 2))) > 600 ? 1 : 0;

		$content = "<?php\n//Discuz! cache file, DO NOT modify!\n//Identify: ".md5($style['name'].$tpldir.$style['copyright'])."\n\n";
		$content .= "\$styleid = '".$styleid."';\n";
		$content .= "\$tpldir = '".$tpldir."';\n";
		foreach($stylevar as $var => $val) {
			if(in_array($var, array('IMGDIR', 'STATICURL', 'BOARDIMG', 'STYLEIMGDIR', 'TPLDIR'))) {
				$val = str_replace(array('{STATICURL}', '{IMGDIR}', '{STYLEIMGDIR}'), array('static/', 'static/image/common', $stylevar['STYLEIMGDIR']), $val);
			}
			$content .= "\$".$var." = '".addcslashes($val, "'\\")."';\n";
		}
		$content .= "\n?>";
		writetocache('style_'.$styleid, $content);
	}

	savecache('styles', $data);
}
PHP;

if (file_put_contents($cacheStyleFile, $cacheStyleContent)) {
    echo "<span class='success'>✅ Restored source/function/cache/cache_style.php</span><br>";
} else {
    echo "<span class='error'>❌ Failed to write cache_style.php. Check permissions!</span><br>";
}
echo "</div>";

// ---------------------------------------------------------
// STEP 2: Configure Database & Settings
// ---------------------------------------------------------
echo "<div class='box'><h3>Step 2: Configuring Database & Settings</h3>";
// Fix Config CSS Compress
$config_global = DISCUZ_ROOT . './config/config_global.php';
if (file_exists($config_global)) {
    $cfg = file_get_contents($config_global);
    if (strpos($cfg, "['cssjscompress'] = 0") === false) {
        // Need to disable compression
        $cfg = preg_replace("/\['cssjscompress'\]\s*=\s*\d+;/", "['cssjscompress'] = 0;", $cfg);
        if (file_put_contents($config_global, $cfg)) {
            echo "✅ Disabled CSS/JS Compression in config.<br>";
        }
    } else {
        echo "ℹ️ CSS/JS Compression already disabled.<br>";
    }
}

// Enable Style 1
C::t('common_setting')->update('styleid', 1);
DB::update('common_style', ['available' => 1], ['styleid' => 1]);
echo "✅ Set Default Style ID 1 (Available = 1).<br>";
echo "</div>";

// ---------------------------------------------------------
// STEP 3: Clear & Rebuild Cache
// ---------------------------------------------------------
echo "<div class='box'><h3>Step 3: Clearing & Rebuilding Cache</h3>";

$cacheDirs = ['./data/cache/', './data/template/'];
foreach ($cacheDirs as $dir) {
    if (is_dir(DISCUZ_ROOT . $dir)) {
        $files = glob(DISCUZ_ROOT . $dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && basename($file) != 'index.htm') {
                @unlink($file);
            }
        }
    }
}
echo "✅ Old cache files cleared.<br>";

// Trigger Rebuild
require_once libfile('function/cache');
updatecache(); // Updates all caches including style
echo "✅ <b>updatecache()</b> function executed.<br>";
echo "</div>";

// ---------------------------------------------------------
// STEP 4: Validation
// ---------------------------------------------------------
echo "<div class='box'><h3>Step 4: Validation</h3>";
$targetCSS = DISCUZ_ROOT . './data/cache/style_1_common.css';
if (file_exists($targetCSS)) {
    $size = filesize($targetCSS);
    echo "<h2 class='success'>🎉 SUCCESS! CSS File Generated!</h2>";
    echo "File: style_1_common.css ($size bytes)<br>";
    echo "<br><a href='index.php' style='display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;'>GO TO HOMEPAGE</a>";
} else {
    echo "<h2 class='error'>❌ FAILURE! CSS File still missing.</h2>";
    echo "This means the system could not generate the file. Check server error logs.";
}
echo "</div></body></html>";
?>