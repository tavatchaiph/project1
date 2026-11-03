# SKRU OBE-QA Smart Audit (MVP)

เว็บแอปต้นแบบ PHP + JavaScript + MySQL สำหรับช่วยตรวจเล่มหลักสูตรด้าน PLO และหลักฐาน OBE/AUN-QA ด้วยหน้าตาโทนสีสุภาพแบบ modern (navy, teal, off-white)

## ความสามารถใน MVP
- Dashboard และรายการเอกสารตรวจ
- อัปโหลด PDF / DOCX / TXT (ไม่เกิน 20 MB)
- ดึงข้อความจาก TXT, DOCX (ต้องมี PHP ZipArchive), PDF (ต้องติดตั้ง `pdftotext`)
- ตรวจคำสำคัญ/คำกริยา PLO เบื้องต้นโดยไม่ใช้ AI API
- เชื่อม AI API ที่รองรับ OpenAI Chat Completions ได้ผ่าน environment variables
- บันทึกผลตรวจใน MySQL และให้กรรมการยืนยันสถานะ/เขียนความเห็น
- Master Checklist ตัวอย่าง 13 ข้อ
- ส่งออกผลตรวจ CSV และพิมพ์จากเบราว์เซอร์

## ความต้องการระบบ
- PHP 8.1+ (extensions: pdo_mysql, mbstring, fileinfo; ZipArchive สำหรับ DOCX)
- MySQL 8+ หรือ MariaDB ที่รองรับ InnoDB
- `pdftotext` จาก Poppler สำหรับอ่าน PDF
- เว็บเซิร์ฟเวอร์ Apache/Nginx ที่ตั้งค่า upload size ได้อย่างน้อย 20 MB

## ติดตั้งแบบ local (XAMPP)
1. แตก ZIP แล้วคัดลอกโฟลเดอร์ `skru_obe_qa_auditor` ไปไว้ใน `htdocs`
2. เปิด phpMyAdmin แล้ว Import `schema.sql`
3. ตรวจ `config.php` ให้ DB_HOST, DB_NAME, DB_USER, DB_PASS ตรงกับเครื่อง
4. สร้างโฟลเดอร์ `storage/uploads` และให้ PHP เขียนได้
5. เปิด `http://localhost/skru_obe_qa_auditor/`

## เปิดใช้ AI API (ไม่บังคับ)
ตั้ง environment variables ก่อนเริ่ม PHP:
- `AI_API_KEY` = API key ของผู้ให้บริการ
- `AI_BASE_URL` = เช่น `https://api.openai.com/v1`
- `AI_MODEL` = ชื่อโมเดลที่บัญชีใช้งานได้

ห้ามฝัง API key ใน JavaScript หรือ commit ลง Git. ในตัวอย่างนี้ระบบส่งข้อความที่ดึงได้สูงสุดประมาณ 45,000 ตัวอักษรไปให้ API ตรวจ; ต้องแจ้งผู้ใช้และผ่านนโยบายข้อมูลของหน่วยงานก่อนใช้งานจริง

## ข้อจำกัดที่ควรรู้
- เป็น MVP ไม่ใช่ระบบ production: ยังไม่มี login/role, audit log ที่สมบูรณ์, malware scanning, OCR, การแยกหน้าจาก PDF แบบแม่นยำ, version comparison หรือ workflow อนุมัติ
- `pdftotext` แบบพื้นฐานไม่ได้รับประกัน page reference ที่แม่นยำ จึงแสดงหน้าเป็น “ยังระบุไม่ได้” แทนการเดา
- หากไม่มี API key จะใช้ heuristic scan เท่านั้น ไม่ใช่การวิเคราะห์ AI เชิงลึก
- ผลจาก AI เป็นข้อเสนอแนะ ต้องให้กรรมการตรวจต้นฉบับและยืนยันทุกครั้ง
- Checklist เป็น baseline ที่ต้องให้ผู้รับผิดชอบมาตรฐาน SKRU ตรวจทานกับคู่มือฉบับทางการก่อนใช้ตัดสิน

## Security checklist ก่อน deploy
- เปิด HTTPS และระบบ Login/Role-based access
- ย้ายไฟล์อัปโหลดออกจาก public web root
- ตั้งค่า `upload_max_filesize`, `post_max_size` และจำกัด MIME/file signatures
- เปิด malware scanning, rate limiting, logging และ backup
- เพิ่มการจัดการข้อมูลส่วนบุคคลและนโยบาย retention
- ปิดการแสดง PHP errors ต่อผู้ใช้
