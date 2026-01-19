-- Table structure for table `tb_komentar_elearning`

CREATE TABLE `tb_komentar_elearning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materi_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('siswa','guru','admin') NOT NULL,
  `komentar` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_komentar_materi` (`materi_id`),
  CONSTRAINT `fk_komentar_materi` FOREIGN KEY (`materi_id`) REFERENCES `tb_materi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
