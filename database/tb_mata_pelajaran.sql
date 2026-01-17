-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2026 at 02:31 AM
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
(2, 'Bahasa Indonesia'),
(3, 'Bahasa Inggris'),
(4, 'IPA'),
(5, 'IPS'),
(1, 'Matematika'),
(6, 'Pendidikan Agama'),
(8, 'Penjaskes'),
(7, 'PKN'),
(9, 'Seni Budaya'),
(10, 'TIK / Informatika');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_mata_pelajaran`
--
ALTER TABLE `tb_mata_pelajaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_mapel` (`nama_mapel`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_mata_pelajaran`
--
ALTER TABLE `tb_mata_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
