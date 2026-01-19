-- Add parent_id column to existing tb_komentar_elearning table
ALTER TABLE `tb_komentar_elearning` 
ADD COLUMN `parent_id` int(11) DEFAULT NULL AFTER `materi_id`,
ADD KEY `fk_komentar_parent` (`parent_id`),
ADD CONSTRAINT `fk_komentar_parent` FOREIGN KEY (`parent_id`) REFERENCES `tb_komentar_elearning` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
