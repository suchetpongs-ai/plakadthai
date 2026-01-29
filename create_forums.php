<?php
/**
 * Discuz! X3.5 Forum Creator Script for Plakadthai.com
 * Place this file in the root directory (same as admin.php) and run it via browser.
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'forum_creator');

require './source/class/class_core.php';

$discuz = C::app();
$discuz->init();

if(!$_G['uid'] || $_G['adminid'] != 1) {
    die('Please login as Administrator first!');
}

echo "<h1>Creating Categories for Plakadthai.com</h1>";

// Function to create category
function create_forum($name, $type = 'group', $fup = 0) {
    global $_G;
    
    // Check if exists
    $exists = C::t('forum_forum')->fetch_all_by_name($name);
    if($exists) {
        foreach($exists as $forum) {
            if($forum['fup'] == $fup) return $forum['fid'];
        }
    }

    $data = array(
        'fup' => $fup,
        'type' => $type,
        'name' => $name,
        'status' => 1,
        'displayorder' => 0,
        'styleid' => 0,
        'allowsmilies' => 1,
        'allowhtml' => 0,
        'allowbbcode' => 1,
        'allowimgcode' => 1,
        'allowmediacode' => 1,
        'allowanonymous' => 0,
        'allowpostspecial' => 1,
        'allowspecialonly' => 0,
        'allowappend' => 0,
        'alloweditrules' => 0,
        'allowfeed' => 1,
        'recyclebin' => 1,
        'modnewposts' => 0,
        'jammer' => 0,
        'disablewatermark' => 0,
        'inheritedmod' => 0,
        'autoclose' => 0,
        'forumcolumns' => 0,
        'threadcaches' => 0,
        'allowside' => 0,
        'shownav' => 0,
        'simple' => 0, 
        'modworks' => 0,
        'allowglobalstick' => 1,
        'level' => $fup ? 0 : 1, // Will be updated by trigger
        'commoncredits' => 0,
        'archive' => 0,
        'recommend' => 0,
        'favtimes' => 0,
        'sharetimes' => 0,
        'disablethumb' => 0,
        'disablecollect' => 0,
    );
    
    $fid = C::t('forum_forum')->insert($data, true);
    
    // Insert into forum_forumfield
    $field_data = array(
        'fid' => $fid,
        'description' => '',
        'password' => '',
        'icon' => '',
        'redirect' => '',
        'attachextensions' => '',
        'rules' => '',
        'seokeywords' => '',
        'seodescription' => '',
        'supe_pushsetting' => '',
        'modrecommend' => '',
        'threadtypes' => '',
        'threadsorts' => '',
        'creditspolicy' => '',
        'formulaperm' => '',
        'domain' => ''
    );
    C::t('forum_forumfield')->insert($field_data);
    
    echo "Created " . ($type == 'group' ? "Category" : "Forum") . ": <strong>$name</strong> (FID: $fid)<br>";
    return $fid;
}

// Structure
$structure = [
    '🏆 1. โซนปลากัด (Betta World)' => [
        'ปลากัดครีบสั้น (Plakat)',
        'ปลากัดครีบยาว (Long Fin)',
        'ปลากัดป่า (Wild Betta)',
        'ปลากัดสีและสายพันธุ์ใหม่',
        'เทคนิคการเพาะพันธุ์ (Breeding)'
    ],
    '🐠 2. ปลาสวยงามอื่นๆ (Other Species)' => [
        'ปลาหางนกยูง (Guppy)',
        'ปลาทอง (Goldfish)',
        'ปลาหมอสี (Cichlids)',
        'ปลาคาร์ป (Koi)',
        'ปลาอโรวาน่า/มังกร (Arowana)',
        'ปลาน้ำจืดขนาดเล็ก (Nano Fish)',
        'ปลาทะเล (Marine Fish)'
    ],
    '📚 3. คลินิกและอุปกรณ์ (Clinic & Equipment)' => [
        'โรงพยาบาลปลา (Fish Hospital)',
        'อาหารและโภชนาการ (Food & Nutrition)',
        'อุปกรณ์และตู้ปลา (Tank & Equipment)',
        'DIY อุปกรณ์เลี้ยงปลา'
    ],
    '🛒 4. ตลาดซื้อ-ขาย (Marketplace)' => [
        'ซื้อ-ขาย ปลากัด (Betta Market)',
        'ซื้อ-ขาย ปลาสวยงามอื่นๆ (General Fish Market)',
        'ประมูลปลา (Auction House)',
        'ซื้อ-ขาย อุปกรณ์ (Equipment Market)',
        'ร้านค้าแนะนำ (Verified Seller)'
    ],
    '☕ 5. มุมพักผ่อน (Community Lounge)' => [
        'โชว์ปลาสวยงาม (Showroom)',
        'ข่าวสารวงการปลา (News)',
        'พูดคุยทั่วไป (General Chat)'
    ]
];

foreach ($structure as $cat => $forums) {
    $cat_fid = create_forum($cat, 'group', 0);
    foreach ($forums as $forum) {
        create_forum($forum, 'forum', $cat_fid);
    }
    echo "<hr>";
}

echo "<h2>✅ Finished! Please delete this file.</h2>";
?>
