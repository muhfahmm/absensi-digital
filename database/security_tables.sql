-- Security Enhancement Database Schema
-- Run this SQL script to add security features to the database

-- ============================================
-- 1. Create Failed Login Attempts Table
-- ============================================
CREATE TABLE IF NOT EXISTS tb_failed_login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME NOT NULL,
    user_agent TEXT,
    INDEX idx_username (username),
    INDEX idx_ip_address (ip_address),
    INDEX idx_attempt_time (attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Create Security Logs Table
-- ============================================
CREATE TABLE IF NOT EXISTS tb_security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    username VARCHAR(100),
    ip_address VARCHAR(45),
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_username (username),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Add Security Fields to tb_siswa
-- ============================================
ALTER TABLE tb_siswa 
ADD COLUMN IF NOT EXISTS account_locked_until DATETIME NULL,
ADD COLUMN IF NOT EXISTS failed_login_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL;

-- ============================================
-- 4. Add Security Fields to tb_guru
-- ============================================
ALTER TABLE tb_guru 
ADD COLUMN IF NOT EXISTS account_locked_until DATETIME NULL,
ADD COLUMN IF NOT EXISTS failed_login_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL;

-- ============================================
-- 5. Add Security Fields to tb_admin
-- ============================================
ALTER TABLE tb_admin 
ADD COLUMN IF NOT EXISTS account_locked_until DATETIME NULL,
ADD COLUMN IF NOT EXISTS failed_login_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL;

-- ============================================
-- 6. Create Refresh Tokens Table (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS tb_refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_role ENUM('admin', 'siswa', 'guru') NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    revoked BOOLEAN DEFAULT FALSE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user (user_id, user_role),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. Create Indexes for Better Performance
-- ============================================
-- Add indexes to existing tables if not exist
ALTER TABLE tb_siswa ADD INDEX IF NOT EXISTS idx_nis (nis);
ALTER TABLE tb_guru ADD INDEX IF NOT EXISTS idx_nuptk (nuptk);
ALTER TABLE tb_admin ADD INDEX IF NOT EXISTS idx_username (username);

-- ============================================
-- 8. Sample Security Event for Testing
-- ============================================
INSERT INTO tb_security_logs (event_type, username, ip_address, details) 
VALUES ('system_init', 'system', '127.0.0.1', '{"message": "Security tables created successfully"}');

-- ============================================
-- Success Message
-- ============================================
SELECT 'Security tables created successfully!' AS message;
