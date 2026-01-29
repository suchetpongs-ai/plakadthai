# คู่มือติดตั้ง Discuz! X3.5 Thai บน VPS

## สำหรับ plakadthai.com

---

## ขั้นตอนที่ 1: เตรียม VPS

ตรวจสอบ DNS ของ domain ให้ชี้ไปยัง IP ของ VPS ก่อน:

```
plakadthai.com     → [IP ของ VPS]
www.plakadthai.com → [IP ของ VPS]
```

---

## ขั้นตอนที่ 2: SSH เข้า VPS

```bash
ssh root@[IP_VPS]
```

---

## ขั้นตอนที่ 3: ดาวน์โหลด Script

```bash
# สร้างโฟลเดอร์และดาวน์โหลด script
mkdir -p /root/discuz-setup
cd /root/discuz-setup

# อัพโหลด deploy_discuz.sh ไปยัง VPS หรือสร้างไฟล์ใหม่
nano deploy_discuz.sh
# (วางเนื้อหาจากไฟล์ deploy_discuz.sh)

# ให้สิทธิ์ execute
chmod +x deploy_discuz.sh
```

---

## ขั้นตอนที่ 4: รัน Script

```bash
./deploy_discuz.sh
```

Script จะ:
1. ✅ อัพเดท system
2. ✅ ติดตั้ง Nginx, PHP 8.2, MariaDB
3. ✅ ดาวน์โหลด Discuz! X3.5 Thai
4. ✅ ตั้งค่า permissions
5. ✅ สร้าง database
6. ✅ ตั้งค่า Nginx
7. ❓ ติดตั้ง SSL (ถามก่อน)

---

## ขั้นตอนที่ 5: ติดตั้งผ่าน Web Browser

1. เปิด browser ไปที่: `http://plakadthai.com/install/`

2. **หน้าเงื่อนไขการใช้งาน**: คลิก "ฉันยอมรับ"

3. **ตรวจสอบ Environment**: ควรเป็น ✅ ทั้งหมด

4. **ตั้งค่า Database**:
   - Database Server: `localhost`
   - Database Name: `discuz_db`
   - Database User: `discuz_user`
   - Database Password: (ดูจาก output ของ script หรือ `/root/discuz_credentials.txt`)

5. **ตั้งค่า Admin**:
   - Admin Username: (ตั้งเอง)
   - Admin Password: (ตั้งเอง)
   - Admin Email: (ใส่ email จริง)

6. คลิก "ติดตั้ง"

---

## ขั้นตอนที่ 6: หลังติดตั้งเสร็จ

### ⚠️ สำคัญมาก: ลบโฟลเดอร์ install

```bash
rm -rf /var/www/plakadthai.com/install
```

### เข้า Admin Panel

- URL: `http://plakadthai.com/admin.php`
- ใช้ username/password ที่ตั้งไว้ตอนติดตั้ง

---

## คำสั่งที่มีประโยชน์

```bash
# ดู credentials
cat /root/discuz_credentials.txt

# Restart services
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart mariadb

# ดู error logs
tail -f /var/log/nginx/plakadthai.com.error.log

# ตั้งค่า SSL ภายหลัง
certbot --nginx -d plakadthai.com -d www.plakadthai.com
```

---

## Troubleshooting

### ปัญหา: หน้าเว็บขึ้น 502 Bad Gateway
```bash
systemctl restart php8.2-fpm
systemctl restart nginx
```

### ปัญหา: Permission denied
```bash
chown -R www-data:www-data /var/www/plakadthai.com
chmod -R 755 /var/www/plakadthai.com
```

### ปัญหา: Database connection failed
```bash
# ตรวจสอบ MariaDB
systemctl status mariadb

# ดู credentials
cat /root/discuz_credentials.txt
```

---

## Server Requirements

| Component | Version |
|-----------|---------|
| OS | Ubuntu 20.04+ / Debian 11+ |
| PHP | 8.2 (พร้อม extensions) |
| Database | MariaDB 10.6+ |
| Web Server | Nginx 1.18+ |
