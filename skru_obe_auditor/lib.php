<?php
require_once __DIR__.'/config.php';
function db(): PDO {
 static $pdo=null; if($pdo) return $pdo;
 $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES=>false
 ]); return $pdo;
}
function h($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('คำขอหมดอายุ กรุณาย้อนกลับและลองใหม่');} }
function redirect(string $url): never { header('Location: '.$url); exit; }
function ensure_storage(): void { if(!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0750,true); }
function extract_text(string $path,string $ext): string {
 if($ext==='txt') return (string)file_get_contents($path);
 if($ext==='pdf' && function_exists('shell_exec')) {
  $cmd='pdftotext -layout '.escapeshellarg($path).' - 2>/dev/null';
  $out=shell_exec($cmd); if(is_string($out)&&trim($out)!=='') return $out;
 }
 if($ext==='docx' && class_exists('ZipArchive')) {
  $z=new ZipArchive(); if($z->open($path)===true){$xml=$z->getFromName('word/document.xml');$z->close();if($xml){$xml=preg_replace('/<\/w:p>/',''."\n",$xml);return html_entity_decode(strip_tags($xml),ENT_QUOTES|ENT_XML1,'UTF-8');}}
 }
 return '';
}
function default_checklist(): array {
 return [
 ['PLO-01','คุณภาพ PLO','PLO ใช้คำกริยาที่สังเกตและประเมินได้','SK-OBE03 / OBE','major'],
 ['PLO-02','คุณภาพ PLO','PLO ระบุผลลัพธ์การเรียนรู้ที่ชัดเจน ไม่ใช่หัวข้อเนื้อหา','SK-OBE03 / OBE','major'],
 ['PLO-03','คุณภาพ PLO','หลีกเลี่ยงคำกำกวม เช่น รู้ เข้าใจ มีความรู้ โดยไม่มีพฤติกรรมกำกับ','SK-OBE03 / OBE','major'],
 ['PLO-04','คุณภาพ PLO','PLO หนึ่งข้อไม่รวมหลายผลลัพธ์ที่แตกต่างกันมากเกินไป','SK-OBE03 / OBE','review'],
 ['PLO-05','คุณภาพ PLO','PLO มีความเหมาะสมกับระดับคุณวุฒิและผู้สำเร็จการศึกษา','OBE','major'],
 ['PLO-06','ที่มาของ PLO','มีหลักฐาน Stakeholder needs รองรับการกำหนด PLO','SKRU / AUN-QA Criterion 1','major'],
 ['PLO-07','ที่มาของ PLO','เชื่อมโยง Stakeholder needs → Objectives → PLO ได้','OBE / AUN-QA Criterion 1','major'],
 ['PLO-08','ความครอบคลุม','พิจารณาความครอบคลุม Knowledge, Skills, Ethics และ Personal Characteristics ตามบริบท','มาตรฐานคุณวุฒิ / OBE','review'],
 ['PLO-09','การประเมิน','แต่ละ PLO สามารถกำหนดวิธีประเมินโดยตรงและเกณฑ์ตัดสินได้','OBE / AUN-QA Criterion 4','major'],
 ['PLO-10','Curriculum Mapping','มีรายวิชาหรือกิจกรรมรองรับการพัฒนาและประเมิน PLO','SK-OBE04 / AUN-QA Criterion 2','major'],
 ['PLO-11','ความสอดคล้อง','PLO เชื่อมโยงกับ CLO การจัดการเรียนรู้ และการประเมินผล','Constructive Alignment','major'],
 ['PLO-12','หลักฐาน','มีเลขหน้า/หลักฐานที่ตรวจสอบย้อนกลับได้สำหรับข้อสรุป','Evidence-based audit','review'],
 ['AUN-01','AUN-QA readiness','แยกหลักฐานที่อยู่ในเล่มออกจากหลักฐานที่ต้องขอนอกเล่ม','AUN-QA Criteria 1–8','review'],
 ];
}
function analyze_basic(string $text): array {
 $findings=[]; $lines=preg_split('/\R/u',$text); $ploLines=[];
 foreach($lines as $i=>$line){ if(preg_match('/\bPLO\s*[-.]?\s*\d+|ผลลัพธ์การเรียนรู้ระดับหลักสูตร|ผลลัพธ์การเรียนรู้ของหลักสูตร/u',$line)) $ploLines[]=[$i+1,trim($line)]; }
 $all=mb_substr($text,0,250000);
 if(!$ploLines){
  $findings[]=['check_id'=>'PLO-EXTRACT','category'=>'การอ่านเอกสาร','title'=>'ยังตรวจไม่พบข้อความ PLO อย่างชัดเจน','status'=>'review','severity'=>'critical','evidence'=>'ไม่พบรูปแบบ PLO 1, PLO-1 หรือหัวข้อผลลัพธ์การเรียนรู้ระดับหลักสูตรในข้อความที่ดึงได้','page_no'=>null,'recommendation'=>'ตรวจว่าไฟล์เป็น PDF ที่มีข้อความจริงหรือไม่ หากเป็นเอกสารสแกนให้ทำ OCR แล้วอัปโหลดใหม่ หรือระบุหัวข้อ PLO ให้ชัดเจน','source'=>'การตรวจเบื้องต้นของระบบ'];
 } else {
  foreach($ploLines as [$lineNo,$line]){
   if(mb_strlen($line)<12) continue;
   $ambiguous=['เข้าใจ','มีความรู้','มีความเข้าใจ','ตระหนัก','ทราบ'];
   foreach($ambiguous as $word){if(mb_strpos($line,$word)!==false){
    $findings[]=['check_id'=>'PLO-VERB','category'=>'คุณภาพ PLO','title'=>'ตรวจทานคำกริยาที่อาจวัดได้ไม่ชัดเจน','status'=>'review','severity'=>'major','evidence'=>$line,'page_no'=>null,'recommendation'=>'พิจารณาระบุพฤติกรรมที่สังเกตและประเมินได้ เช่น อธิบาย วิเคราะห์ ประยุกต์ใช้ ประเมิน หรือออกแบบ โดยเลือกคำให้ตรงกับเจตนาของ PLO','source'=>'Heuristic scan · บรรทัดข้อความ '.$lineNo];break;
   }}
   if(preg_match('/และ.{0,30}(วิเคราะห์|ประเมิน|ออกแบบ|สร้าง|ประยุกต์|ปฏิบัติ)/u',$line)){
    $findings[]=['check_id'=>'PLO-MULTI','category'=>'คุณภาพ PLO','title'=>'ตรวจสอบว่ามีหลายผลลัพธ์ใน PLO เดียวหรือไม่','status'=>'review','severity'=>'review','evidence'=>$line,'page_no'=>null,'recommendation'=>'ตรวจว่าคำกริยาแต่ละคำเป็นส่วนของสมรรถนะเดียวกันหรือควรแยกเป็น PLO ย่อย ทั้งนี้ระบบยังไม่สามารถตัดสินจากคำเชื่อมเพียงอย่างเดียว','source'=>'Heuristic scan · บรรทัดข้อความ '.$lineNo];
   }
  }
  $findings[]=['check_id'=>'PLO-MANUAL','category'=>'คุณภาพ PLO','title'=>'ยืนยันรายการ PLO และระดับ Bloom โดยผู้ตรวจ','status'=>'review','severity'=>'review','evidence'=>'พบข้อความที่อาจเกี่ยวข้องกับ PLO จำนวน '.count($ploLines).' บรรทัดในข้อความที่ดึงได้','page_no'=>null,'recommendation'=>'ตรวจ PLO ทีละข้อ ยืนยัน Action Verb, Learning Statement, Qualifying Phrase, Domain/Bloom และความสามารถในการประเมิน ก่อนสรุปผล','source'=>'ผลสแกนอัตโนมัติ'];
 }
 $checks=[
 ['Stakeholder','stakeholder|ผู้มีส่วนได้ส่วนเสีย|ผู้ใช้บัณฑิต|ศิษย์เก่า'],
 ['Objectives','วัตถุประสงค์ของหลักสูตร|วัตถุประสงค์หลักสูตร'],
 ['Curriculum Mapping','Curriculum Mapping|ตารางความสัมพันธ์|PLO.*รายวิชา'],
 ['Assessment','ประเมินผลลัพธ์การเรียนรู้|PLO Assessment|เกณฑ์การประเมิน'],
 ['CQI','ปรับปรุงอย่างต่อเนื่อง|Continuous Improvement|ทบทวนหลักสูตร']
 ];
 foreach($checks as [$name,$pattern]){
  if(!preg_match('/'.$pattern.'/iu',$all)) $findings[]=['check_id'=>'DOC-'.$name,'category'=>'ความครบถ้วนของหลักฐาน','title'=>'ไม่พบคำสำคัญเกี่ยวกับ '.$name,'status'=>'missing','severity'=>'review','evidence'=>'ไม่พบคำสำคัญตามรูปแบบที่ระบบค้นหาในข้อความที่ดึงได้','page_no'=>null,'recommendation'=>'ตรวจเอกสารด้วยตนเองก่อนสรุปว่าไม่มีข้อมูล เพราะอาจใช้คำศัพท์อื่น ตารางภาพ หรือข้อความที่ระบบดึงไม่ได้','source'=>'Keyword scan'];
 }
 return $findings;
}
function ai_review(string $text,string $title): ?array {
 if(!AI_API_KEY || !function_exists('curl_init')) return null;
 $prompt="คุณเป็นผู้ช่วยตรวจคุณภาพ PLO สำหรับหลักสูตรมหาวิทยาลัยราชภัฏสงขลา ตาม OBE และ AUN-QA Programme Level v4.0. ตรวจเฉพาะหลักฐานในเอกสาร ห้ามสร้างเลขหน้า/ข้อกำหนดที่ไม่มีหลักฐาน แยก 'ไม่พบในข้อความที่อ่านได้' ออกจาก 'ไม่มีระบบ'. ตอบ JSON object: {summary:string, findings:[{check_id:string,category:string,title:string,status:'pass'|'review'|'missing'|'critical',severity:'critical'|'major'|'review',evidence:string,page_no:number|null,recommendation:string,source:string}]} สูงสุด 25 findings. ถ้าระบุหน้าไม่ได้ใช้ null. เนื้อหา:\n".mb_substr($text,0,45000);
 $payload=json_encode(['model'=>AI_MODEL,'messages'=>[['role'=>'system','content'=>'ตอบเป็น JSON ที่ถูกต้องเท่านั้น'],['role'=>'user','content'=>$prompt]],'temperature'=>0.1,'response_format'=>['type'=>'json_object']],JSON_UNESCAPED_UNICODE);
 $ch=curl_init(rtrim(AI_BASE_URL,'/').'/chat/completions');curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.AI_API_KEY],CURLOPT_POSTFIELDS=>$payload,CURLOPT_TIMEOUT=>90]);
 $res=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if(!$res||$code>=400)return null;
 $data=json_decode($res,true);$content=$data['choices'][0]['message']['content']??'';$obj=json_decode($content,true);return is_array($obj)?$obj:null;
}
