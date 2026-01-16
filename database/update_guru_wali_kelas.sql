-- Menambahkan kolom id_kelas_wali pada tabel tb_guru
-- Kolom ini menunjukkan kelas mana yang diampu oleh guru tersebut sebagai Wali Kelas.
-- Bersifat, boleh NULL jika guru tersebut bukan wali kelas.

USE db_absensi_digital;

ALTER TABLE tb_guru
ADD COLUMN id_kelas_wali INT DEFAULT NULL,
ADD CONSTRAINT fk_guru_kelas_wali FOREIGN KEY (id_kelas_wali) REFERENCES tb_kelas(id) ON DELETE SET NULL;
