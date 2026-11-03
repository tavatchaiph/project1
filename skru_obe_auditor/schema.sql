CREATE DATABASE IF NOT EXISTS skru_obe_audit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skru_obe_audit;
CREATE TABLE IF NOT EXISTS projects (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(255) NOT NULL,
 program_type VARCHAR(60) NOT NULL DEFAULT 'ไม่ระบุ',
 program_kind VARCHAR(60) NOT NULL DEFAULT 'ไม่ระบุ',
 original_filename VARCHAR(255) NOT NULL,
 stored_filename VARCHAR(255) NOT NULL,
 extracted_text LONGTEXT,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_projects_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS audit_findings (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 project_id BIGINT UNSIGNED NOT NULL,
 check_id VARCHAR(40) NOT NULL,
 category VARCHAR(120) NOT NULL,
 title VARCHAR(500) NOT NULL,
 status ENUM('pass','review','missing','critical') NOT NULL DEFAULT 'review',
 severity ENUM('critical','major','review') NOT NULL DEFAULT 'review',
 evidence TEXT,
 page_no INT NULL,
 recommendation TEXT,
 source VARCHAR(255),
 reviewer_comment TEXT,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL,
 CONSTRAINT fk_findings_project FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
 INDEX idx_findings_project(project_id),
 INDEX idx_findings_status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
