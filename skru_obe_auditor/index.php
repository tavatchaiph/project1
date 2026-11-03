<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib.php';
$pdo = db();
$projects = $pdo->query("SELECT p.*, (SELECT COUNT(*) FROM audit_findings f WHERE f.project_id=p.id) finding_count FROM projects p ORDER BY p.created_at DESC LIMIT 8")->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) projects, (SELECT COUNT(*) FROM audit_findings) findings, (SELECT COUNT(*) FROM audit_findings WHERE status='critical') critical, (SELECT COUNT(*) FROM audit_findings WHERE status='review') review FROM projects")->fetch();
$recent = $pdo->query("SELECT f.*,p.title project_title FROM audit_findings f JOIN projects p ON p.id=f.project_id ORDER BY f.created_at DESC LIMIT 5")->fetchAll();
?><!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SKRU OBE-QA Smart Audit</title><link rel="stylesheet" href="assets/style.css"></head><body>
<?php include 'partials/nav.php'; ?>
<main class="container">
<section class="hero"><div><div class="eyebrow">CURRICULUM QUALITY • SKRU</div><h1>ตรวจเล่มหลักสูตรอย่างเป็นระบบ<br><span>ด้วย OBE + AUN-QA</span></h1><p>อัปโหลดเอกสาร วิเคราะห์ PLO ตรวจหลักฐาน และติดตามประเด็นที่ต้องแก้ไขในพื้นที่ทำงานเดียว</p><a class="btn btn-primary" href="upload.php">＋ เริ่มตรวจเล่มหลักสูตร</a></div><div class="hero-art"><div class="art-circle"></div><div class="art-card"><div class="art-icon">✓</div><b>Evidence-based audit</b><small>ตรวจสอบย้อนกลับได้</small><div class="art-lines"><i></i><i></i><i></i></div></div><div class="art-tag">OBE · PLO · QA</div></div></section>
<section class="stats-grid">
<div class="stat"><div class="stat-label">เอกสารที่ตรวจ</div><div class="stat-value"><?= (int)$stats['projects'] ?></div><div class="stat-note">หลักสูตรในระบบ</div></div>
<div class="stat"><div class="stat-label">ประเด็นที่พบ</div><div class="stat-value"><?= (int)$stats['findings'] ?></div><div class="stat-note">ผลตรวจทั้งหมด</div></div>
<div class="stat"><div class="stat-label">ประเด็นสำคัญ</div><div class="stat-value"><?= (int)$stats['critical'] ?></div><div class="stat-note">ต้องตรวจทานก่อน</div></div>
<div class="stat"><div class="stat-label">รอยืนยัน</div><div class="stat-value"><?= (int)$stats['review'] ?></div><div class="stat-note">ต้องให้กรรมการพิจารณา</div></div>
</section>
<section class="section-head"><div><div class="eyebrow">WORKSPACE</div><h2>งานตรวจล่าสุด</h2><p>เปิดผลตรวจเพื่อดูหลักฐาน หน้าเอกสาร และข้อเสนอแนะ</p></div><a class="btn btn-outline" href="projects.php">ดูทั้งหมด →</a></section>
<?php if(!$projects): ?><div class="empty"><div class="empty-icon">⌁</div><h3>ยังไม่มีเอกสารในระบบ</h3><p>เริ่มจากอัปโหลดเล่มหลักสูตร PDF, DOCX หรือ TXT</p><a class="btn btn-primary" href="upload.php">อัปโหลดเอกสาร</a></div><?php else: ?>
<div class="project-list"><?php foreach($projects as $p): ?><a class="project-row" href="project.php?id=<?=(int)$p['id']?>"><div class="file-badge">DOC</div><div class="project-main"><b><?=h($p['title'])?></b><small><?=h($p['program_type'])?> · <?=h(date('d/m/Y H:i',strtotime($p['created_at'])))?></small></div><div class="row-count"><b><?=(int)$p['finding_count']?></b><small>ประเด็น</small></div><span class="arrow">→</span></a><?php endforeach; ?></div><?php endif; ?>
<section class="section-head"><div><div class="eyebrow">AUDIT METHOD</div><h2>กรอบการตรวจหลัก</h2><p>ระบบช่วยวิเคราะห์เบื้องต้น โดยผลสุดท้ายต้องผ่านการยืนยันของผู้ตรวจ</p></div></section>
<div class="module-grid"><div class="module"><span>01</span><h3>คุณภาพ PLO</h3><p>Action verb, ความชัดเจน, ความสามารถในการวัด และ Bloom’s Taxonomy</p></div><div class="module"><span>02</span><h3>ที่มาของ PLO</h3><p>ความเชื่อมโยง Stakeholder needs → Objectives → PLO</p></div><div class="module"><span>03</span><h3>หลักฐานและ Mapping</h3><p>ค้นหาข้อมูลสนับสนุน ช่องว่าง และประเด็นที่ต้องตรวจต่อ</p></div><div class="module"><span>04</span><h3>AUN-QA Readiness</h3><p>จัดกลุ่มหลักฐานที่พบและสิ่งที่ต้องตรวจเพิ่มนอกเล่มหลักสูตร</p></div></div>
<footer>SKRU OBE-QA Smart Audit <span>AI-assisted review · Human decision required</span></footer></main></body></html>