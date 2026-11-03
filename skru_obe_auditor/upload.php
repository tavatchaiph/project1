<?php
require_once __DIR__.'/lib.php'; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); ensure_storage();
 $title=trim($_POST['title']??'');$type=$_POST['program_type']??'ไม่ระบุ';$kind=$_POST['program_kind']??'ไม่ระบุ';
 if($title==='')$error='กรุณาระบุชื่อหลักสูตร';
 elseif(!isset($_FILES['document'])||$_FILES['document']['error']!==UPLOAD_ERR_OK)$error='กรุณาเลือกไฟล์เอกสาร';
 else{
  $f=$_FILES['document'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
  if(!in_array($ext,['pdf','docx','txt']))$error='รองรับเฉพาะ PDF, DOCX และ TXT';
  elseif($f['size']>MAX_UPLOAD_BYTES)$error='ไฟล์ต้องมีขนาดไม่เกิน 20 MB';
  else{
   $safe=bin2hex(random_bytes(16)).'.'.$ext;$path=UPLOAD_DIR.'/'.$safe;
   if(!move_uploaded_file($f['tmp_name'],$path))$error='ไม่สามารถจัดเก็บไฟล์ได้';
   else{
    $text=extract_text($path,$ext);
    $stmt=db()->prepare("INSERT INTO projects(title,program_type,program_kind,original_filename,stored_filename,extracted_text,created_at) VALUES(?,?,?,?,?,?,NOW())");
    $stmt->execute([$title,$type,$kind,$f['name'],$safe,$text]);$id=(int)db()->lastInsertId();
    $findings=analyze_basic($text);
    $ai=ai_review($text,$title);
    if($ai && !empty($ai['findings'])) $findings=$ai['findings'];
    $ins=db()->prepare("INSERT INTO audit_findings(project_id,check_id,category,title,status,severity,evidence,page_no,recommendation,source,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,NOW())");
    foreach($findings as $x)$ins->execute([$id,$x['check_id']??'AI-REVIEW',$x['category']??'ผลตรวจ AI',$x['title']??'ประเด็นตรวจ',$x['status']??'review',$x['severity']??'review',$x['evidence']??'',isset($x['page_no'])&&$x['page_no']!==''?$x['page_no']:null,$x['recommendation']??'',($x['source']??($ai?'AI review':'Basic text scan'))]);
    redirect('project.php?id='.$id.'&created=1');
   }
  }
 }
}
?><!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>อัปโหลดเล่มหลักสูตร</title><link rel="stylesheet" href="assets/style.css"></head><body><?php include 'partials/nav.php'; ?><main class="container"><div class="page-title"><div class="eyebrow">NEW AUDIT</div><h1>อัปโหลดเล่มหลักสูตร</h1><p>ระบบจะดึงข้อความและตรวจ PLO เบื้องต้น พร้อมสร้างรายการให้ผู้ตรวจยืนยัน</p></div><?php if($error):?><div class="alert"><?=h($error)?></div><?php endif;?><form method="post" enctype="multipart/form-data" class="panel"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><h2>ข้อมูลเอกสาร</h2><div class="form-grid"><div class="field full"><label>ชื่อหลักสูตร *</label><input name="title" required placeholder="เช่น หลักสูตรบริหารธุรกิจบัณฑิต พ.ศ. 2570"></div><div class="field"><label>ระดับหลักสูตร</label><select name="program_type"><option>ปริญญาตรี</option><option>ปริญญาโท</option><option>ปริญญาเอก</option><option>ไม่ระบุ</option></select></div><div class="field"><label>ประเภทเอกสาร</label><select name="program_kind"><option>หลักสูตรปรับปรุง</option><option>หลักสูตรใหม่</option><option>ฉบับแก้ไข</option><option>ไม่ระบุ</option></select></div><div class="field full"><label>ไฟล์หลักสูตร *</label><div class="upload-zone"><div style="font-size:28px;color:var(--teal)">⇧</div><b>เลือกไฟล์ PDF, DOCX หรือ TXT</b><p class="hint">ขนาดสูงสุด 20 MB · PDF ที่เป็นภาพสแกนอาจต้อง OCR ก่อน</p><input type="file" name="document" accept=".pdf,.docx,.txt" required></div></div><div class="field full"><div class="alert">ข้อควรทราบ: ระบบต้นแบบใช้การดึงข้อความและตรวจคำสำคัญเป็นค่าเริ่มต้น หากตั้งค่า AI API ระบบจะส่งข้อความบางส่วนไปวิเคราะห์เพิ่มเติม โปรดหลีกเลี่ยงการอัปโหลดข้อมูลส่วนบุคคลที่ไม่จำเป็น</div><button class="btn btn-primary" type="submit">เริ่มตรวจเอกสาร →</button> <a class="btn btn-outline" href="index.php">ยกเลิก</a></div></div></form><footer>SKRU OBE-QA Smart Audit <span>ตรวจทานผลโดยผู้เชี่ยวชาญก่อนใช้อ้างอิง</span></footer></main><script src="assets/app.js"></script></body></html>