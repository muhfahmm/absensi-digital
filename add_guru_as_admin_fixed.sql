-- Skrip Perbaikan: Menambahkan/Update Guru menjadi Admin
-- ===================================================

-- 1. Pastikan Struktur Tabel Admin Siap (Safe to run multiple times)
--    Menambahkan kolom NIP, Foto, QR Code ke tb_admin jika belum ada.
DROP PROCEDURE IF EXISTS upgrade_admin_table;
DELIMITER //
CREATE PROCEDURE upgrade_admin_table()
BEGIN
    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_admin' AND COLUMN_NAME = 'nip') THEN
        ALTER TABLE tb_admin ADD COLUMN nip VARCHAR(30) NULL AFTER id;
    END IF;
    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_admin' AND COLUMN_NAME = 'foto_profil') THEN
        ALTER TABLE tb_admin ADD COLUMN foto_profil VARCHAR(255) NULL AFTER nama_lengkap;
    END IF;
    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_admin' AND COLUMN_NAME = 'kode_qr') THEN
        ALTER TABLE tb_admin ADD COLUMN kode_qr VARCHAR(255) NULL AFTER foto_profil;
    END IF;
END//
DELIMITER ;
CALL upgrade_admin_table();
DROP PROCEDURE upgrade_admin_table;


-- 2. Tambah ATAU Update Data Guru
--    Menggunakan ON DUPLICATE KEY UPDATE agar tidak error jika NIP/Username sudah ada.
INSERT INTO tb_guru (nip, username, nama_lengkap, password, kode_qr, foto_profil, created_at)
VALUES (
    '199001012024011001', 
    'sahabat_guru', 
    'Ahmad Guru, S.Pd.', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'GURU-199001012024011001-AUTO', 
    NULL, 
    NOW()
)
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    nama_lengkap = VALUES(nama_lengkap),
    password = VALUES(password),
    kode_qr = VALUES(kode_qr);


-- 3. Tambahkan ke Data Admin (Sinkronisasi)
--    Juga menggunakan ON DUPLICATE KEY UPDATE agar aman dijalankan berulang.
INSERT INTO tb_admin (username, password, nama_lengkap, nip, kode_qr, foto_profil, created_at)
VALUES (
    'sahabat_guru', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Ahmad Guru, S.Pd.', 
    '199001012024011001', 
    'GURU-199001012024011001-AUTO', 
    NULL, 
    NOW()
)
ON DUPLICATE KEY UPDATE
    password = VALUES(password),
    nama_lengkap = VALUES(nama_lengkap),
    nip = VALUES(nip),
    kode_qr = VALUES(kode_qr);

-- Selesai.
