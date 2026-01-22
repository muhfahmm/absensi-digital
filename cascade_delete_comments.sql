-- Trigger untuk Siswa
DROP TRIGGER IF EXISTS after_siswa_delete;
DELIMITER $$
CREATE TRIGGER after_siswa_delete
AFTER DELETE ON tb_siswa
FOR EACH ROW
BEGIN
    DELETE FROM tb_komentar_elearning 
    WHERE user_id = OLD.id AND role = 'siswa';
END$$
DELIMITER ;

-- Trigger untuk Guru
DROP TRIGGER IF EXISTS after_guru_delete;
DELIMITER $$
CREATE TRIGGER after_guru_delete
AFTER DELETE ON tb_guru
FOR EACH ROW
BEGIN
    DELETE FROM tb_komentar_elearning 
    WHERE user_id = OLD.id AND role = 'guru';
END$$
DELIMITER ;

-- Trigger untuk Admin
DROP TRIGGER IF EXISTS after_admin_delete;
DELIMITER $$
CREATE TRIGGER after_admin_delete
AFTER DELETE ON tb_admin
FOR EACH ROW
BEGIN
    DELETE FROM tb_komentar_elearning 
    WHERE user_id = OLD.id AND role = 'admin';
END$$
DELIMITER ;
