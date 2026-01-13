-- Database Schema for Absensi Digital

CREATE DATABASE IF NOT EXISTS db_absensi_digital;
USE db_absensi_digital;

-- 1. Tabel Admin
-- Untuk login halaman admin panel
CREATE TABLE IF NOT EXISTS tb_admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Kelas
-- Menyimpan data kelas dan token QR kelas
CREATE TABLE IF NOT EXISTS tb_kelas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kelas VARCHAR(50) NOT NULL,
    jumlah_siswa INT DEFAULT 0, -- Field untuk memanajemen jumlah siswa (manual input sesuai request)
    token_kelas VARCHAR(100) UNIQUE, -- Text yang akan digenerate menjadi QR Code
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Siswa
-- Data siswa dengan relasi ke kelas
CREATE TABLE IF NOT EXISTS tb_siswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nis VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Password untuk login siswa
    id_kelas INT,
    kode_qr VARCHAR(100) UNIQUE, -- Token unik individu untuk QR Code
    foto_profil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kelas) REFERENCES tb_kelas(id) ON DELETE SET NULL
);

-- 4. Tabel Guru
-- Data guru
CREATE TABLE IF NOT EXISTS tb_guru (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(20) UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    kode_qr VARCHAR(100) UNIQUE,
    foto_profil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabel Karyawan
-- Data karyawan (Staff TU, Kebersihan, dll)
CREATE TABLE IF NOT EXISTS tb_karyawan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(20) UNIQUE, 
    nama_lengkap VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    jabatan VARCHAR(50),
    kode_qr VARCHAR(100) UNIQUE,
    foto_profil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabel Absensi
-- Mencatat seluruh riwayat absensi
CREATE TABLE IF NOT EXISTS tb_absensi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL, -- ID dari tabel siswa/guru/karyawan
    role ENUM('siswa', 'guru', 'karyawan') NOT NULL, -- Pembeda asal tabel ID
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    jam_keluar TIME,
    status ENUM('hadir', 'sakit', 'izin', 'alpa', 'terlambat') DEFAULT 'alpa',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Index untuk mempercepat pencarian history
    INDEX (user_id, role, tanggal)
);

-- Insert Default Admin (Password: admin123)
-- Gunakan hash PHP password_hash('admin123', PASSWORD_DEFAULT) nanti di aplikasi
-- Ini hanya contoh row dummy
INSERT INTO tb_admin (username, password, nama_lengkap) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
