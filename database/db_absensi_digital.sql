-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2026 at 11:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_absensi`
--

CREATE TABLE `tb_absensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('siswa','guru','karyawan') NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','sakit','izin','alpa','terlambat') DEFAULT 'alpa',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_absensi`
--

INSERT INTO `tb_absensi` (`id`, `user_id`, `role`, `tanggal`, `jam_masuk`, `jam_keluar`, `status`, `keterangan`, `created_at`) VALUES
(34, 14, 'siswa', '2026-01-16', '21:15:30', NULL, 'terlambat', NULL, '2026-01-16 14:15:30');

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id` int(11) NOT NULL,
  `nuptk` varchar(30) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `kode_qr` varchar(255) DEFAULT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `guru_mapel_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id`, `nuptk`, `username`, `password`, `nama_lengkap`, `foto_profil`, `kode_qr`, `id_kelas`, `guru_mapel_id`, `created_at`) VALUES
(13, NULL, 'admin', '$2y$10$Dxhv6CdA3FJ8SJXCwwXPI.Rkh5840j.G5nS9wJMTP1WsX1bNvPC.W', 'admin', NULL, NULL, NULL, NULL, '2026-01-16 15:02:14'),
(25, '11', 'guru 1', '$2y$10$KWSKW2jR/v1IKYurOYUtieCoT9CJPZl4ta0faBB7mKO6T0KCGkDkO', 'guru 1', 'GURU_11_1768643453.jpg', 'GURU-11-696b5b7cef8da', NULL, NULL, '2026-01-17 09:50:53'),
(26, '22', 'guru 2', '$2y$10$khAvIjprPnWQD1J18orRRuBdaBjo8T01eOR7XtXI4EoB2GsRYNw16', 'guru 2', 'GURU_22_1768643507.jpg', 'GURU-22-696b5bb3a8f07', NULL, NULL, '2026-01-17 09:51:47'),
(27, '33', 'guru 3', '$2y$10$18dtkUW/t3Aoa82DLsuyXOrvIZiXwRx0EMdETB5om1XfRo0Zj9kIe', 'guru 3', 'GURU_33_1768643559.jpg', 'GURU-33-696b5be7a7d53', NULL, NULL, '2026-01-17 09:52:39'),
(28, '44', 'guru 4', '$2y$10$y72ty0l4pZYloWEFwZoAP.A.3Vc1vwtTtmPQkLOUXQtFEiZvUucoO', 'guru 4', 'GURU_44_1768643619.jpg', 'GURU-44-696b5c234ef4b', NULL, NULL, '2026-01-17 09:53:39'),
(29, '55', 'guru 5', '$2y$10$1509DIxxVTNSP/V4O5hWJucbU/QHM3zndwyuPVyhSMEgm5VVPVIm.', 'guru 5', 'GURU_55_1768643643.jpg', 'GURU-55-696b5c3bd167c', NULL, NULL, '2026-01-17 09:54:03'),
(30, '66', 'guru 6', '$2y$10$qYruFvAKmpJSnirusRs./ef6FYK3BQtiOY/YlWwnuvGHRtiMmBJnS', 'guru 6', 'GURU_66_1768644117.jpg', 'GURU-66-696b5e15d9253', NULL, NULL, '2026-01-17 10:01:57');

-- --------------------------------------------------------

--
-- Table structure for table `tb_guru`
--

CREATE TABLE `tb_guru` (
  `id` int(11) NOT NULL,
  `nuptk` varchar(20) DEFAULT NULL,
  `guru_mapel_id` int(11) DEFAULT NULL,
  `guru_mapel` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `kode_qr` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_kelas_wali` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_guru`
--

INSERT INTO `tb_guru` (`id`, `nuptk`, `guru_mapel_id`, `guru_mapel`, `username`, `nama_lengkap`, `password`, `kode_qr`, `foto_profil`, `created_at`, `id_kelas_wali`) VALUES
(13, '11', 16, NULL, 'guru 1', 'guru 1', '$2y$10$KWSKW2jR/v1IKYurOYUtieCoT9CJPZl4ta0faBB7mKO6T0KCGkDkO', 'GURU-11-696b5b7cef8da', 'GURU_11_1768643453.jpg', '2026-01-17 09:50:53', NULL),
(14, '22', 18, NULL, 'guru 2', 'guru 2', '$2y$10$khAvIjprPnWQD1J18orRRuBdaBjo8T01eOR7XtXI4EoB2GsRYNw16', 'GURU-22-696b5bb3a8f07', 'GURU_22_1768643507.jpg', '2026-01-17 09:51:47', NULL),
(15, '33', 17, NULL, 'guru 3', 'guru 3', '$2y$10$18dtkUW/t3Aoa82DLsuyXOrvIZiXwRx0EMdETB5om1XfRo0Zj9kIe', 'GURU-33-696b5be7a7d53', 'GURU_33_1768643559.jpg', '2026-01-17 09:52:39', NULL),
(16, '44', 15, NULL, 'guru 4', 'guru 4', '$2y$10$y72ty0l4pZYloWEFwZoAP.A.3Vc1vwtTtmPQkLOUXQtFEiZvUucoO', 'GURU-44-696b5c234ef4b', 'GURU_44_1768643619.jpg', '2026-01-17 09:53:39', NULL),
(17, '55', 14, NULL, 'guru 5', 'guru 5', '$2y$10$1509DIxxVTNSP/V4O5hWJucbU/QHM3zndwyuPVyhSMEgm5VVPVIm.', 'GURU-55-696b5c3bd167c', 'GURU_55_1768643643.jpg', '2026-01-17 09:54:03', NULL),
(18, '66', 19, NULL, 'guru 6', 'guru 6', '$2y$10$qYruFvAKmpJSnirusRs./ef6FYK3BQtiOY/YlWwnuvGHRtiMmBJnS', 'GURU-66-696b5e15d9253', 'GURU_66_1768644117.jpg', '2026-01-17 10:01:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_karyawan`
--

CREATE TABLE `tb_karyawan` (
  `id` int(11) NOT NULL,
  `nuptk` varchar(20) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(50) DEFAULT NULL,
  `kode_qr` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_kelas`
--

CREATE TABLE `tb_kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `jumlah_siswa` int(11) DEFAULT 0,
  `token_kelas` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_guru_wali` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kelas`
--

INSERT INTO `tb_kelas` (`id`, `nama_kelas`, `jumlah_siswa`, `token_kelas`, `created_at`, `id_guru_wali`) VALUES
(1, 'X-1', 10, 'KELAS-X-1-2026', '2026-01-13 13:00:03', NULL),
(2, 'X-2', 10, 'KELAS-X-2-2026', '2026-01-13 13:10:48', NULL),
(3, 'XI-1', 20, 'KELAS-XI-1-2026', '2026-01-13 13:23:57', NULL),
(4, 'XI-2', 20, 'KELAS-XI-2-2026', '2026-01-13 13:24:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_log_wali`
--

CREATE TABLE `tb_log_wali` (
  `id` int(11) NOT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `id_guru` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_mata_pelajaran`
--

CREATE TABLE `tb_mata_pelajaran` (
  `id` int(11) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_mata_pelajaran`
--

INSERT INTO `tb_mata_pelajaran` (`id`, `nama_mapel`) VALUES
(16, 'ASJ'),
(18, 'Keamanan Jaringan'),
(19, 'mapel 1'),
(17, 'Nirkabel'),
(15, 'PKPJ'),
(14, 'PPJ');

-- --------------------------------------------------------

--
-- Table structure for table `tb_siswa`
--

CREATE TABLE `tb_siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `kode_qr` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_siswa`
--

INSERT INTO `tb_siswa` (`id`, `nis`, `username`, `nama_lengkap`, `password`, `id_kelas`, `kode_qr`, `foto_profil`, `created_at`) VALUES
(14, '1', 'siswa 1', 'siswa 1', '$2y$10$Oc3lDbLVnTlDibmT9EockevntlAixJC2.ABU0egufhOkdjrYPcOM6', 1, 'SISWA-1-696a09826d84a', 'SISWA_1_1768556930.jpg', '2026-01-16 09:48:50'),
(15, '2', 'siswa 2', 'siswa 2', '$2y$10$.imZS5D/G.EfcjllDPlz1OFdIvTKFnWhdlVuT51gBc01mOZNYpKuW', 2, 'SISWA-2-696a20d0c33fb', 'SISWA_2_1768562896.jpg', '2026-01-16 11:28:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_absensi`
--
ALTER TABLE `tb_absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`role`,`tanggal`);

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_admin_kelas` (`id_kelas`),
  ADD KEY `fk_admin_mapel` (`guru_mapel_id`);

--
-- Indexes for table `tb_guru`
--
ALTER TABLE `tb_guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nuptk`),
  ADD UNIQUE KEY `kode_qr` (`kode_qr`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nuptk` (`nuptk`),
  ADD KEY `fk_guru_kelas_wali` (`id_kelas_wali`),
  ADD KEY `fk_guru_mapel` (`guru_mapel_id`);

--
-- Indexes for table `tb_karyawan`
--
ALTER TABLE `tb_karyawan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nuptk`),
  ADD UNIQUE KEY `kode_qr` (`kode_qr`),
  ADD UNIQUE KEY `nuptk` (`nuptk`);

--
-- Indexes for table `tb_kelas`
--
ALTER TABLE `tb_kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_kelas` (`token_kelas`),
  ADD KEY `fk_wali_kelas` (`id_guru_wali`);

--
-- Indexes for table `tb_log_wali`
--
ALTER TABLE `tb_log_wali`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indexes for table `tb_mata_pelajaran`
--
ALTER TABLE `tb_mata_pelajaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_mapel` (`nama_mapel`);

--
-- Indexes for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `kode_qr` (`kode_qr`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_absensi`
--
ALTER TABLE `tb_absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tb_guru`
--
ALTER TABLE `tb_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tb_karyawan`
--
ALTER TABLE `tb_karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_kelas`
--
ALTER TABLE `tb_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_log_wali`
--
ALTER TABLE `tb_log_wali`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_mata_pelajaran`
--
ALTER TABLE `tb_mata_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD CONSTRAINT `fk_admin_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_admin_mapel` FOREIGN KEY (`guru_mapel_id`) REFERENCES `tb_mata_pelajaran` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_guru`
--
ALTER TABLE `tb_guru`
  ADD CONSTRAINT `fk_guru_kelas_wali` FOREIGN KEY (`id_kelas_wali`) REFERENCES `tb_kelas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_guru_mapel` FOREIGN KEY (`guru_mapel_id`) REFERENCES `tb_mata_pelajaran` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_kelas`
--
ALTER TABLE `tb_kelas`
  ADD CONSTRAINT `fk_wali_kelas` FOREIGN KEY (`id_guru_wali`) REFERENCES `tb_guru` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_log_wali`
--
ALTER TABLE `tb_log_wali`
  ADD CONSTRAINT `tb_log_wali_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_log_wali_ibfk_2` FOREIGN KEY (`id_guru`) REFERENCES `tb_guru` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  ADD CONSTRAINT `tb_siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
