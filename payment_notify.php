<?php
/**
 * Payment Slip Notification for Plakadthai.com
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'payment_notify');

require './source/class/class_core.php';

$discuz = C::app();
$discuz->init();

if (!$_G['uid']) {
    showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$navtitle = 'แจ้งโอนเงิน (Notify Payment)';
$msg = '';

if (submitcheck('submit_slip')) {
    $amount = $_GET['amount'];
    $date = $_GET['paydate'];
    $time = $_GET['paytime'];
    $note = $_GET['note'];

    // Check file upload
    if ($_FILES['slip']['error'] == 0) {
        $uploadDir = DISCUZ_ROOT . './data/payment_slips/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
        $newFilename = date('Ymd_His') . '_' . $_G['uid'] . '.' . $ext;
        $targetFile = $uploadDir . $newFilename;

        if (move_uploaded_file($_FILES['slip']['tmp_name'], $targetFile)) {
            // Save to DB (Using common_member_log or create new table if needed)
            // For simplicity, we append to a text log for now, can be upgraded to DB later
            $logEntry = date('Y-m-d H:i:s') . " | UID: {$_G['uid']} | User: {$_G['username']} | Amount: $amount | File: $newFilename | Note: $note\n";
            file_put_contents($uploadDir . 'transactions.log', $logEntry, FILE_APPEND);

            $msg = '<div class="alert_success" style="padding:15px; background:#e6fffa; border:1px solid #b2f5ea; color:#2c7a7b; margin-bottom:20px; border-radius:5px;">✅ แจ้งโอนเงินสำเร็จ! แอดมินจะตรวจสอบและเติมเงินให้ภายใน 24 ชม.</div>';
        } else {
            $msg = '<div class="alert_error" style="color:red;">❌ อัพโหลดสลิปไม่สำเร็จ</div>';
        }
    } else {
        $msg = '<div class="alert_error" style="color:red;">❌ กรุณาแนบไฟล์สลิป</div>';
    }
}

include template('common/header');
?>

<style>
    .notify-box {
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"],
    input[type="time"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .btn-submit {
        background: #28a745;
        color: white !important;
        padding: 10px 30px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
</style>

<div class="notify-box">
    <h1>📝 แจ้งชำระเงิน</h1>
    <p class="mbw">กรอกรายละเอียดและแนบสลิปเพื่อยืนยัน</p>

    <?php echo $msg; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">

        <div class="form-group">
            <label>จำนวนเงิน (บาท):</label>
            <input type="number" name="amount" step="0.01" required placeholder="เช่น 100.00">
        </div>

        <div class="form-group">
            <label>วันที่โอน:</label>
            <input type="date" name="paydate" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label>เวลาที่โอน (ตามสลิป):</label>
            <input type="time" name="paytime" value="<?php echo date('H:i'); ?>" required>
        </div>

        <div class="form-group">
            <label>หลักฐานการโอน (สลิป):</label>
            <input type="file" name="slip" accept="image/*" required style="padding:10px 0;">
        </div>

        <div class="form-group">
            <label>หมายเหตุ (ถ้ามี):</label>
            <input type="text" name="note" placeholder="เช่น โอนเข้ากสิกร">
        </div>

        <div style="text-align:center; margin-top:20px;">
            <button type="submit" name="submit_slip" value="true" class="btn-submit">ยืนยันการแจ้งโอน</button>
        </div>
    </form>
</div>

<?php
include template('common/footer');
?>