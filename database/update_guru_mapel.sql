-- 1. Buat Tabel Master Mata Pelajaran (Best Practice untuk relasi)
-- Kami membuat tabel ini karena 'guru_mapel_id' memerlukan tabel referensi yang berisi daftar pelajaran.
CREATE TABLE IF NOT EXISTS tb_mata_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_mapel VARCHAR(100) NOT NULL UNIQUE
);

-- 2. Isi Data Dummy Mata Pelajaran
INSERT IGNORE INTO tb_mata_pelajaran (nama_mapel) VALUES 
('Matematika'), 
('Bahasa Indonesia'), 
('Bahasa Inggris'), 
('IPA'), 
('IPS'), 
('Pendidikan Agama'), 
('PKN'), 
('Penjaskes'), 
('Seni Budaya'), 
('TIK / Informatika');

-- 3. Update Tabel Guru untuk Relasi ID
ALTER TABLE tb_guru 
ADD COLUMN guru_mapel_id INT NULL AFTER nip,
ADD CONSTRAINT fk_guru_mapel
FOREIGN KEY (guru_mapel_id) REFERENCES tb_mata_pelajaran(id) ON DELETE SET NULL;
