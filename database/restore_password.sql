-- Restore Password requirement
-- Set default password '123456' untuk data yang kosong
UPDATE tb_siswa SET password = '$2y$10$lnDz.lrrXVz9o3BkHdX9R.JDIETwTkyciUQLi/N7gMMDpLelrD/2O' WHERE password IS NULL OR password = '';
UPDATE tb_guru SET password = '$2y$10$lnDz.lrrXVz9o3BkHdX9R.JDIETwTkyciUQLi/N7gMMDpLelrD/2O' WHERE password IS NULL OR password = '';

-- Kembalikan kolom password menjadi NOT NULL (Wajib)
ALTER TABLE tb_siswa MODIFY password VARCHAR(255) NOT NULL;
ALTER TABLE tb_guru MODIFY password VARCHAR(255) NOT NULL;
