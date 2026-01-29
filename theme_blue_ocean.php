<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'themeblue');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

// DEFINING THE BLUE OCEAN CSS
$customCSS = "
/* =========================================
   THEME: BLUE OCEAN (PLAKADTHAI EDITION)
   Designed by: AI Assistant
   ========================================= */

/* 1. IMPORT MODERN THAI FONT (PROMPT) */
@import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap');
body, input, button, select, textarea, a, .xs1, .xs2, .xi1, .xi2, .z, .y, .hm, .xg1, .xg2 {
    font-family: 'Prompt', sans-serif !important;
}

/* 2. BACKGROUND & BODY */
body {
    background: #e3f2fd !important; /* Light Blue Water */
    background-image: linear-gradient(0deg, #e3f2fd 0%, #f1f8ff 100%);
    color: #333;
}

/* 3. MAIN CONTAINER (CARD STYLE) */
.wp {
    width: 1180px !important;
    max-width: 96% !important;
    margin: 20px auto !important;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05); /* Soft Shadow */
    padding: 20px;
    box-sizing: border-box;
}

/* 4. HEADER BAR (DEEP OCEAN) */
#toptb {
    background: #0d47a1 !important; /* Deep Blue */
    border-bottom: 1px solid #002171;
    color: #fff !important;
}
#toptb a { color: #bbdefb !important; }

/* 5. NAVIGATION BAR (GRADIENT) */
.nv {
    background: linear-gradient(90deg, #0288d1 0%, #01579b 100%) !important;
    border-radius: 8px;
    margin-top: 10px;
    box-shadow: 0 4px 6px rgba(0,87,155,0.2);
}
.nv li a {
    color: #fff !important;
    font-weight: 500;
    font-size: 15px;
}
.nv li.a a, .nv li a:hover {
    background: rgba(255,255,255,0.2) !important;
}

/* 6. FORUM HEADERS */
.fl .bm_h {
    background: #f1f8e9 !important; /* Reset */
    background-image: linear-gradient(to right, #0277bd, #039be5) !important; 
    border-radius: 8px 8px 0 0;
}
.fl .bm_h h2 a { color: #fff !important; }
.fl .bm_h .o img { filter: brightness(0) invert(1); } /* White Toggle Icon */

/* 7. BUTTONS (PRIMARY) */
.pn.pnpost button, #postsubmit, button.pn {
    background: #0091ea !important;
    border: none !important;
    color: #fff !important;
    border-radius: 6px;
    padding: 5px 20px;
    box-shadow: 0 2px 5px rgba(0,145,234,0.3);
    transition: all 0.2s;
}
.pn.pnpost button:hover { transform: translateY(-2px); }

/* 8. LINKS */
a { color: #01579b; transition: color 0.2s; }
a:hover { color: #0288d1; text-decoration: none; }

/* 9. SEARCH BAR */
#scbar {
    background: transparent !important;
    border: none !important;
}
#scbar_txt {
    border: 2px solid #0288d1;
    border-radius: 20px 0 0 20px;
    padding-left: 15px;
}
#scbar_btn {
    background: #0288d1 !important;
    border-radius: 0 20px 20px 0;
    width: 60px;
}

/* 10. FOOTER */
#ft {
    margin-top: 20px;
    border-top: 5px solid #0277bd;
    background: #fff;
    padding: 20px;
}
";

// TARGET FILES (Apply to Default and DiscuzinTH if exists)
$targetFiles = array(
    DISCUZ_ROOT . './template/default/common/extend_common.css',
    DISCUZ_ROOT . './template/discuzinth/common/extend_common.css' // Custom theme path
);

$updated = array();

foreach ($targetFiles as $file) {
    // Only write if directory exists
    if (file_exists(dirname($file))) {
        // Backup? No, just overwrite for "Design it for me" request
        file_put_contents($file, $customCSS);
        $updated[] = $file;
    }
}

// UPDATE CACHE
require_once libfile('function/cache');
updatecache('setting');
updatecache('styles');
// Manually delete style cache files
$files = glob(DISCUZ_ROOT . './data/cache/style_*.css');
foreach ($files as $f)
    unlink($f);

echo "<html><body style='font-family:sans-serif; text-align:center; padding:50px; background:#e3f2fd;'>";
echo "<h1 style='color:#0d47a1'>🎨 Blue Ocean Theme Applied!</h1>";
echo "<p>Updated CSS files:</p><ul>";
foreach ($updated as $u)
    echo "<li>$u</li>";
echo "</ul>";
echo "<h2><a href='index.php' style='display:inline-block; padding:10px 20px; background:#0277bd; color:white; text-decoration:none; border-radius:5px;'>View Your New Website</a></h2>";
echo "</body></html>";
?>