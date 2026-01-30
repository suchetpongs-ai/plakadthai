<?php
/**
 * PromptPay QR Payment for Plakadthai.com
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'payment');

require './source/class/class_core.php';

$discuz = C::app();
$discuz->init();

$navtitle = 'เติมเงิน (Top Up)';
include template('common/header');

// CONFIGURATION - EDIT THIS
$promptPayID = '0812345678'; // ใส่เบอร์โทร (08x...) หรือ เลขบัตรประชาชน (13 หลัก) ของคุณที่นี่
$accountName = 'PLAKADTHAI ADMIN';

?>

<style>
    .payment-box {
        max-width: 500px;
        margin: 50px auto;
        padding: 30px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .qr-container {
        margin: 20px 0;
        padding: 20px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 8px;
        display: none;
    }

    .amount-inputs {
        margin: 20px 0;
    }

    .btn-pay {
        background: #004299;
        color: white !important;
        padding: 10px 30px;
        border-radius: 25px;
        font-size: 18px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-pay:hover {
        background: #003077;
        text-decoration: none;
    }
</style>

<div class="payment-box">
    <h2><img src="static/image/common/bank_logo.png" style="height:30px;vertical-align:middle;"> เติมเงินเข้าระบบ</h2>
    <p class="mbw">โอนเงินผ่าน PromptPay QR Code (ฟรีค่าธรรมเนียม)</p>

    <div class="amount-inputs">
        <p>ระบุจำนวนเงินที่ต้องการเติม (บาท):</p>
        <input type="number" id="payAmount" class="px"
            style="font-size: 20px; padding: 10px; width: 150px; text-align: center;" placeholder="100" value="100">
        <button onclick="generateQR()" class="btn-pay">สร้าง QR Code</button>
    </div>

    <div id="qrArea" class="qr-container">
        <h3 style="color:#004299; margin-bottom:10px;">สแกนเพื่อจ่ายเงิน</h3>
        <div id="qrcode"></div>
        <p style="margin-top:10px; font-weight:bold; font-size:18px;"><span id="showAmount">0</span> บาท</p>
        <p style="color:#666; font-size:12px;">โอนเข้าบัญชี: <?php echo $accountName; ?></p>
        <hr class="da">
        <p style="color:red; font-weight:bold;">⚠️ โอนเสร็จแล้ว โปรดแจ้งโอนเงินทันที</p>
        <a href="payment_notify.php" class="pn pnc"><strong><span>แจ้งโอนเงิน (Upload Slip) &raquo;</span></strong></a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // PromptPay Payload Generator (Standard EMVCo)
    function generatePayload(id, amount) {
        var target = id.replace(/[^0-9]/g, '');
        var targetType = target.length >= 13 ? '02' : '01'; // 01=Mobile, 02=CitizenID
        if (targetType === '01' && target.startsWith('0')) {
            target = '66' + target.substring(1);
        }

        var amountStr = parseFloat(amount).toFixed(2);
        var amountLen = ('00' + amountStr.length).slice(-2);

        var payload = [
            '000201', // Format Indicator
            '010211', // Point of Initiation Method (11=Static, 12=Dynamic)
            '2937',   // Merchant Account Information
            '0016A000000677010111', // AID
            '011300' + target,      // Biller ID
            '5802TH', // Country Code
            '5303764', // Currency
            '54' + amountLen + amountStr, // Amount
            '6304' // CRC Prefix
        ].join('');

        payload += crc16(payload);
        return payload;
    }

    function crc16(data) {
        var crc = 0xFFFF;
        for (var i = 0; i < data.length; i++) {
            var x = ((crc >> 8) ^ data.charCodeAt(i)) & 0xFF;
            x ^= x >> 4;
            crc = ((crc << 8) ^ (x << 12) ^ (x << 5)) & 0xFFFF;
        }
        return ('0000' + crc.toString(16).toUpperCase()).slice(-4);
    }

    var qrcode = new QRCode(document.getElementById("qrcode"), {
        width: 200,
        height: 200
    });

    function generateQR() {
        var amount = document.getElementById('payAmount').value;
        if (amount <= 0) { alert('กรุณาระบุจำนวนเงิน'); return; }

        var ppID = '<?php echo $promptPayID; ?>';
        var payload = generatePayload(ppID, amount);

        qrcode.makeCode(payload);
        document.getElementById('showAmount').innerText = parseFloat(amount).toFixed(2);
        document.getElementById('qrArea').style.display = 'block';
    }
</script>

<?php
include template('common/footer');
?>