<?php
// ตั้งค่าฐานข้อมูลด้วยตัวแปรแวดล้อม หรือแก้ค่าเริ่มต้นด้านล่าง
define('DB_HOST', getenv('DB_HOST') ?: 'your_host_name');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database_name');
define('DB_USER', getenv('DB_USER') ?: 'your_db_user');
define('DB_PASS', getenv('DB_PASS') ?: 'your_db_password');
define('UPLOAD_DIR', __DIR__ . '/your_storage_directory');
define('MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
define('AI_API_KEY', getenv('AI_API_KEY') ?: '');
define('AI_BASE_URL', getenv('AI_BASE_URL') ?: 'https://api.openai.com/v1');
define('AI_MODEL', getenv('AI_MODEL') ?: 'gpt-4.1-mini');
if (session_status() === PHP_SESSION_NONE) session_start();
