-- Tabel untuk Materi E-Learning
-- Menyimpan file atau link yang dibagikan guru ke siswa

CREATE TABLE IF NOT EXISTS tb_elearning_materi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guru INT NOT NULL,               -- Guru pengampu
    id_kelas INT NOT NULL,              -- Target kelas (misal: 1 untuk X-1)
    mapel VARCHAR(100) NOT NULL,        -- Nama Mata Pelajaran (misal: Matematika Wajib)
    judul VARCHAR(200) NOT NULL,        -- Judul Materi
    deskripsi TEXT,                     -- Instruksi atau detail tambahan
    tipe_file VARCHAR(50) NOT NULL,     -- 'pdf', 'pptx', 'docx', 'xlsx', 'image', 'link' (untuk youtube/web)
    file_path VARCHAR(255) NOT NULL,    -- Nama file di server atau URL lengkap jika 'link'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_guru) REFERENCES tb_guru(id) ON DELETE CASCADE,
    FOREIGN KEY (id_kelas) REFERENCES tb_kelas(id) ON DELETE CASCADE
);

-- Tabel untuk Tugas/Submission (Opsional untuk pengembangan selanjutnya)
-- Jika siswa diminta mengupload balik jawaban
CREATE TABLE IF NOT EXISTS tb_elearning_tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materi INT NOT NULL,             -- Referensi ke materi/tugas di atas
    id_siswa INT NOT NULL,              -- Siswa yang mengumpulkan
    file_path VARCHAR(255) NOT NULL,    -- File jawaban siswa
    nilai INT DEFAULT NULL,             -- Nilai dari guru (opsional)
    komentar TEXT,                      -- Komentar guru
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_materi) REFERENCES tb_elearning_materi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_siswa) REFERENCES tb_siswa(id) ON DELETE CASCADE
);
