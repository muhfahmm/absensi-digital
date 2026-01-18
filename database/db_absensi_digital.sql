-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 02:31 AM
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
(37, 14, 'siswa', '2026-01-17', '17:44:19', '19:12:48', 'terlambat', NULL, '2026-01-17 10:44:19');

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
  `username` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `kode_guru` varchar(10) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `kode_qr` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_kelas_wali` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_guru`
--

INSERT INTO `tb_guru` (`id`, `nuptk`, `guru_mapel_id`, `username`, `nama_lengkap`, `kode_guru`, `password`, `kode_qr`, `foto_profil`, `created_at`, `id_kelas_wali`) VALUES
(13, '11', 16, 'guru 1', 'guru 1', 'g1', '$2y$10$KWSKW2jR/v1IKYurOYUtieCoT9CJPZl4ta0faBB7mKO6T0KCGkDkO', 'GURU-11-696b5b7cef8da', 'GURU_11_1768643453.jpg', '2026-01-17 09:50:53', NULL),
(14, '22', 18, 'guru 2', 'guru 2', 'g2', '$2y$10$khAvIjprPnWQD1J18orRRuBdaBjo8T01eOR7XtXI4EoB2GsRYNw16', 'GURU-22-696b5bb3a8f07', 'GURU_22_1768643507.jpg', '2026-01-17 09:51:47', NULL),
(15, '33', 17, 'guru 3', 'guru 3', 'g3', '$2y$10$18dtkUW/t3Aoa82DLsuyXOrvIZiXwRx0EMdETB5om1XfRo0Zj9kIe', 'GURU-33-696b5be7a7d53', 'GURU_33_1768643559.jpg', '2026-01-17 09:52:39', NULL),
(16, '44', 15, 'guru 4', 'guru 4', 'g4', '$2y$10$y72ty0l4pZYloWEFwZoAP.A.3Vc1vwtTtmPQkLOUXQtFEiZvUucoO', 'GURU-44-696b5c234ef4b', 'GURU_44_1768643619.jpg', '2026-01-17 09:53:39', NULL),
(17, '55', 14, 'guru 5', 'guru 5', 'g5', '$2y$10$1509DIxxVTNSP/V4O5hWJucbU/QHM3zndwyuPVyhSMEgm5VVPVIm.', 'GURU-55-696b5c3bd167c', 'GURU_55_1768643643.jpg', '2026-01-17 09:54:03', NULL),
(18, '66', 19, 'guru 6', 'guru 6', 'g6', '$2y$10$qYruFvAKmpJSnirusRs./ef6FYK3BQtiOY/YlWwnuvGHRtiMmBJnS', 'GURU-66-696b5e15d9253', 'GURU_66_1768644117.jpg', '2026-01-17 10:01:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_jadwal_pelajaran`
--

CREATE TABLE `tb_jadwal_pelajaran` (
  `id` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `id_jam` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jadwal_pelajaran`
--

INSERT INTO `tb_jadwal_pelajaran` (`id`, `id_kelas`, `id_mapel`, `id_guru`, `hari`, `id_jam`, `created_at`) VALUES
(1, 1, 16, 13, 'Senin', 1, '2026-01-17 10:57:27'),
(2, 1, 18, 14, 'Senin', 2, '2026-01-17 11:01:13'),
(3, 1, 16, 13, 'Senin', 3, '2026-01-17 11:09:03'),
(6, 1, 17, 18, 'Senin', 4, '2026-01-17 11:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `tb_jam_pelajaran`
--

CREATE TABLE `tb_jam_pelajaran` (
  `id` int(11) NOT NULL,
  `jam_ke` int(11) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `is_istirahat` tinyint(1) DEFAULT 0,
  `keterangan` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jam_pelajaran`
--

INSERT INTO `tb_jam_pelajaran` (`id`, `jam_ke`, `jam_mulai`, `jam_selesai`, `is_istirahat`, `keterangan`, `created_at`) VALUES
(1, 0, '07:00:00', '07:40:00', 0, 'Mentoring', '2026-01-17 10:53:05'),
(2, 1, '07:40:00', '08:20:00', 0, NULL, '2026-01-17 10:53:05'),
(3, 2, '08:20:00', '09:00:00', 0, NULL, '2026-01-17 10:53:05'),
(4, 3, '09:00:00', '09:40:00', 0, NULL, '2026-01-17 10:53:05'),
(5, 99, '09:40:00', '10:00:00', 1, 'Sholat Dhuha', '2026-01-17 10:53:05'),
(6, 4, '10:00:00', '10:40:00', 0, NULL, '2026-01-17 10:53:05'),
(7, 5, '10:40:00', '11:20:00', 0, NULL, '2026-01-17 10:53:05'),
(8, 6, '11:20:00', '12:00:00', 0, NULL, '2026-01-17 10:53:05'),
(9, 98, '12:00:00', '12:35:00', 1, 'Sholat Dhuhur', '2026-01-17 10:53:05'),
(10, 7, '12:35:00', '13:15:00', 0, NULL, '2026-01-17 10:53:05'),
(11, 8, '13:15:00', '13:55:00', 0, NULL, '2026-01-17 10:53:05'),
(12, 9, '13:55:00', '14:35:00', 0, NULL, '2026-01-17 10:53:05'),
(13, 10, '14:35:00', '15:15:00', 0, NULL, '2026-01-17 10:53:05');

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
-- Table structure for table `tb_materi`
--

CREATE TABLE `tb_materi` (
  `id` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `id_mapel` int(11) DEFAULT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_materi`
--

INSERT INTO `tb_materi` (`id`, `id_guru`, `id_mapel`, `id_kelas`, `judul`, `deskripsi`, `file_path`, `tipe_file`, `created_at`) VALUES
(3, 13, NULL, NULL, 'pemberitahuan 1', '', 'MATERI_1768650078_696b755eea885.pdf', 'pdf', '2026-01-17 11:41:18'),
(4, 13, 16, 1, 'pemberitahuan 1', '', 'MATERI_1768657430_696b921610487.jpeg', 'jpeg', '2026-01-17 13:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `tb_riwayat_saldo`
--

CREATE TABLE `tb_riwayat_saldo` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tipe` enum('masuk','keluar') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_saldo`
--

CREATE TABLE `tb_saldo` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('siswa','guru') DEFAULT 'siswa',
  `saldo_saat_ini` decimal(15,2) DEFAULT 0.00,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(14, '1', 'siswa 1', 'siswa 1', '$2y$10$Oc3lDbLVnTlDibmT9EockevntlAixJC2.ABU0egufhOkdjrYPcOM6', 1, 'SISWA-1-696a09826d84a', 'SISWA_1_1768556930.jpg', '2026-01-16 09:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `tb_spp_setting`
--

CREATE TABLE `tb_spp_setting` (
  `id` int(11) NOT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `tahun_ajaran` varchar(20) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_tagihan_spp`
--

CREATE TABLE `tb_tagihan_spp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bulan` int(2) NOT NULL,
  `tahun` int(4) NOT NULL,
  `nominal_tagihan` decimal(15,2) NOT NULL,
  `status_bayar` enum('belum','lunas') DEFAULT 'belum',
  `tanggal_bayar` datetime DEFAULT NULL,
  `id_transaksi_midtrans` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_tagihan_spp`
--

INSERT INTO `tb_tagihan_spp` (`id`, `user_id`, `bulan`, `tahun`, `nominal_tagihan`, `status_bayar`, `tanggal_bayar`, `id_transaksi_midtrans`, `created_at`) VALUES
(1, 14, 1, 2026, 150000.00, 'belum', NULL, NULL, '2026-01-17 18:28:56'),
(2, 14, 2, 2026, 150000.00, 'belum', NULL, NULL, '2026-01-17 19:12:24');

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi_midtrans`
--

CREATE TABLE `tb_transaksi_midtrans` (
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('siswa','guru') DEFAULT 'siswa',
  `gross_amount` decimal(15,2) NOT NULL,
  `tipe_transaksi` enum('spp','topup','kantin') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_status` varchar(20) DEFAULT 'pending',
  `snap_token` varchar(255) DEFAULT NULL,
  `pdf_url` varchar(255) DEFAULT NULL,
  `transaction_time` datetime DEFAULT NULL,
  `settlement_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `tb_jadwal_pelajaran`
--
ALTER TABLE `tb_jadwal_pelajaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jadwal_kelas` (`id_kelas`),
  ADD KEY `fk_jadwal_mapel` (`id_mapel`),
  ADD KEY `fk_jadwal_guru` (`id_guru`),
  ADD KEY `fk_jadwal_jam` (`id_jam`);

--
-- Indexes for table `tb_jam_pelajaran`
--
ALTER TABLE `tb_jam_pelajaran`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `tb_materi`
--
ALTER TABLE `tb_materi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_materi_guru` (`id_guru`),
  ADD KEY `fk_materi_mapel` (`id_mapel`),
  ADD KEY `fk_materi_kelas` (`id_kelas`);

--
-- Indexes for table `tb_riwayat_saldo`
--
ALTER TABLE `tb_riwayat_saldo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_saldo`
--
ALTER TABLE `tb_saldo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_saldo` (`user_id`,`role`);

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
-- Indexes for table `tb_spp_setting`
--
ALTER TABLE `tb_spp_setting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_tagihan_spp`
--
ALTER TABLE `tb_tagihan_spp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tb_transaksi_midtrans`
--
ALTER TABLE `tb_transaksi_midtrans`
  ADD PRIMARY KEY (`order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_absensi`
--
ALTER TABLE `tb_absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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
-- AUTO_INCREMENT for table `tb_jadwal_pelajaran`
--
ALTER TABLE `tb_jadwal_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_jam_pelajaran`
--
ALTER TABLE `tb_jam_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- AUTO_INCREMENT for table `tb_materi`
--
ALTER TABLE `tb_materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_riwayat_saldo`
--
ALTER TABLE `tb_riwayat_saldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_saldo`
--
ALTER TABLE `tb_saldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tb_spp_setting`
--
ALTER TABLE `tb_spp_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_tagihan_spp`
--
ALTER TABLE `tb_tagihan_spp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `tb_jadwal_pelajaran`
--
ALTER TABLE `tb_jadwal_pelajaran`
  ADD CONSTRAINT `fk_jadwal_guru` FOREIGN KEY (`id_guru`) REFERENCES `tb_guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_jam` FOREIGN KEY (`id_jam`) REFERENCES `tb_jam_pelajaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jadwal_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `tb_mata_pelajaran` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `tb_materi`
--
ALTER TABLE `tb_materi`
  ADD CONSTRAINT `fk_materi_guru` FOREIGN KEY (`id_guru`) REFERENCES `tb_guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_materi_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_materi_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `tb_mata_pelajaran` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  ADD CONSTRAINT `tb_siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `tb_kelas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
