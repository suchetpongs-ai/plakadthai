<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🛠️ Restoring cache_style.php Manually...</h1>";

$dir = __DIR__ . '/source/function/cache';
if (!is_dir($dir))
    mkdir($dir, 0777, true);

$file = $dir . '/cache_style.php';

// Discuz X3.5 cache_style.php source code
$code = <<<'PHP'
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

if (file_put_contents($file, $code)) {
    echo "<h2 style='color:green'>✅ File Restored Successfully!</h2>";
    echo "Location: $file <br>";
    echo "Size: " . filesize($file) . " bytes<br>";
    echo "<hr><h3>Now please run <a href='force_style_rebuild.php'>Force Style Rebuild</a> again.</h3>";
} else {
    echo "<h2 style='color:red'>❌ Write Failed! Check Permissions.</h2>";
}
?>