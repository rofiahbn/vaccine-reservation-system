-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 06:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reservation_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_type` enum('Home Service','In Clinic') NOT NULL,
  `nomor_antrian` varchar(20) NOT NULL,
  `tanggal_booking` date NOT NULL,
  `waktu_booking` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `patient_id`, `service_type`, `nomor_antrian`, `tanggal_booking`, `waktu_booking`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(25, 35, 'Home Service', '20260116-001', '2026-01-16', '08:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(26, 35, 'Home Service', '20260116-002', '2026-01-16', '09:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(27, 35, 'Home Service', '20260117-001', '2026-01-17', '10:00:00', 'pending', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(28, 35, 'Home Service', '20260118-001', '2026-01-18', '11:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(29, 36, 'Home Service', '20260116-003', '2026-01-16', '10:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(30, 36, 'Home Service', '20260116-004', '2026-01-16', '13:00:00', 'pending', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(31, 36, 'Home Service', '20260119-001', '2026-01-19', '14:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(32, 36, 'Home Service', '20260121-001', '2026-01-21', '15:00:00', 'pending', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(34, 35, 'Home Service', '2026115-002', '2026-01-15', '09:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(35, 35, 'Home Service', '2026115-003', '2026-01-15', '10:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(36, 35, 'Home Service', '2026115-004', '2026-01-15', '11:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(37, 35, 'Home Service', '2026115-005', '2026-01-15', '13:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(38, 35, 'Home Service', '2026115-006', '2026-01-15', '14:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(39, 35, 'Home Service', '2026115-007', '2026-01-15', '15:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(40, 36, 'Home Service', '2026115-008', '2026-01-15', '16:00:00', 'confirmed', NULL, '0000-00-00 00:00:00', '2026-01-16 21:15:57'),
(41, 38, 'Home Service', '20260122-001', '2026-01-22', '16:15:00', 'pending', 'Pendaftaran online', '2026-01-17 16:12:38', '2026-01-17 16:12:38'),
(42, 64, 'Home Service', '20260124-001', '2026-01-24', '16:15:00', 'pending', 'Pendaftaran online', '2026-01-17 17:21:02', '2026-01-17 17:21:02'),
(43, 65, 'Home Service', '20260129-001', '2026-01-29', '14:00:00', 'pending', 'Pendaftaran online', '2026-01-17 17:21:02', '2026-01-17 17:21:02'),
(44, 66, 'Home Service', '20260129-002', '2026-01-29', '16:15:00', 'pending', 'Pendaftaran online', '2026-01-17 17:21:02', '2026-01-17 17:21:02'),
(45, 67, 'Home Service', '20260128-001', '2026-01-28', '13:45:00', 'pending', 'Pendaftaran online', '2026-01-17 17:21:02', '2026-01-17 17:21:02'),
(46, 68, 'Home Service', '20260130-001', '2026-01-30', '14:15:00', 'pending', 'Pendaftaran online', '2026-01-17 19:51:03', '2026-01-17 19:51:03'),
(47, 69, 'Home Service', '20260130-002', '2026-01-30', '12:15:00', 'pending', 'Pendaftaran online', '2026-01-17 20:39:33', '2026-01-17 20:39:33'),
(48, 70, 'Home Service', '20260130-003', '2026-01-30', '14:30:00', 'pending', 'Pendaftaran online', '2026-01-17 20:39:33', '2026-01-17 20:39:33'),
(49, 71, 'Home Service', '20260130-004', '2026-01-30', '12:30:00', 'pending', 'Pendaftaran online', '2026-01-17 20:48:30', '2026-01-17 20:48:30'),
(50, 72, 'Home Service', '20260130-005', '2026-01-30', '16:00:00', 'pending', 'Pendaftaran online', '2026-01-17 20:51:44', '2026-01-17 20:51:44'),
(51, 73, 'Home Service', '20260130-006', '2026-01-30', '16:15:00', 'pending', 'Pendaftaran online', '2026-01-17 20:51:44', '2026-01-17 20:51:44'),
(52, 75, 'In Clinic', '20260130-007', '2026-01-30', '10:15:00', 'pending', 'Pendaftaran online', '2026-01-18 11:24:13', '2026-01-18 11:24:13'),
(53, 77, 'In Clinic', '20260130-008', '2026-01-30', '16:30:00', 'pending', 'Pendaftaran online', '2026-01-18 11:44:39', '2026-01-18 11:44:39'),
(54, 78, 'In Clinic', '20260130-009', '2026-01-30', '14:00:00', 'pending', 'Pendaftaran online', '2026-01-18 11:57:47', '2026-01-18 11:57:47');

-- --------------------------------------------------------

--
-- Table structure for table `booking_services`
--

CREATE TABLE `booking_services` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_klinik`
--

CREATE TABLE `jadwal_klinik` (
  `id` int(11) NOT NULL,
  `hari_week` int(11) DEFAULT NULL COMMENT '1=Minggu, 2=Senin, ..., 7=Sabtu',
  `jam_buka` time DEFAULT NULL,
  `jam_tutup` time DEFAULT NULL,
  `status` enum('buka','tutup') DEFAULT 'buka'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_klinik`
--

INSERT INTO `jadwal_klinik` (`id`, `hari_week`, `jam_buka`, `jam_tutup`, `status`) VALUES
(1, 2, '09:00:00', '16:30:00', 'buka'),
(2, 3, '09:00:00', '16:30:00', 'buka'),
(3, 4, '09:00:00', '16:30:00', 'buka'),
(4, 5, '09:00:00', '16:30:00', 'buka'),
(5, 6, '09:00:00', '16:30:00', 'buka'),
(6, 7, '09:00:00', '16:30:00', 'buka');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_libur`
--

CREATE TABLE `jadwal_libur` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `jenis` enum('nasional','khusus','minggu') DEFAULT 'nasional'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_libur`
--

INSERT INTO `jadwal_libur` (`id`, `tanggal`, `keterangan`, `jenis`) VALUES
(2, '2024-03-11', 'Hari Raya Nyepi', 'nasional'),
(3, '2024-04-10', 'Idul Fitri 1445 H', 'nasional'),
(4, '2024-05-01', 'Hari Buruh Internasional', 'nasional'),
(5, '2024-05-09', 'Kenaikan Isa Almasih', 'nasional'),
(6, '2024-06-01', 'Hari Lahir Pancasila', 'nasional'),
(7, '2024-08-17', 'Hari Kemerdekaan RI', 'nasional'),
(25, '2026-01-01', 'Tahun Baru Masehi', 'nasional'),
(26, '2026-01-16', 'Isra Mi’raj Nabi Muhammad SAW', 'nasional'),
(27, '2026-02-17', 'Tahun Baru Imlek 2577 Kongzili', 'nasional'),
(28, '2026-03-19', 'Hari Suci Nyepi', 'nasional'),
(29, '2026-03-21', 'Hari Raya Idul Fitri (1)', 'nasional'),
(30, '2026-03-22', 'Hari Raya Idul Fitri (2)', 'nasional'),
(31, '2026-04-03', 'Wafat Yesus Kristus', 'nasional'),
(32, '2026-04-05', 'Hari Kebangkitan Yesus Kristus (Paskah)', 'nasional'),
(33, '2026-05-01', 'Hari Buruh Internasional', 'nasional'),
(34, '2026-05-14', 'Kenaikan Yesus Kristus', 'nasional'),
(35, '2026-05-27', 'Hari Raya Idul Adha 1447 H', 'nasional'),
(36, '2026-05-31', 'Hari Raya Waisak 2570 BE', 'nasional'),
(37, '2026-06-01', 'Hari Lahir Pancasila', 'nasional'),
(38, '2026-06-16', '1 Muharram 1448 H', 'nasional'),
(39, '2026-08-17', 'Hari Proklamasi Kemerdekaan RI', 'nasional'),
(40, '2026-08-25', 'Maulid Nabi Muhammad SAW', 'nasional'),
(41, '2026-12-25', 'Kelahiran Yesus Kristus (Natal)', 'nasional');

-- --------------------------------------------------------

--
-- Table structure for table `kipi_records`
--

CREATE TABLE `kipi_records` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `kipi_date` date NOT NULL,
  `symptoms` text DEFAULT NULL,
  `severity` enum('Ringan','Sedang','Berat') DEFAULT 'Ringan',
  `action_taken` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `record_date` datetime NOT NULL,
  `keluhan` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `no_rekam_medis` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nama_panggilan` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  `usia` int(11) DEFAULT NULL,
  `kategori_usia` enum('Anak','Dewasa') NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `paspor` varchar(20) DEFAULT NULL,
  `kebangsaan` varchar(50) DEFAULT 'Indonesia',
  `pekerjaan` varchar(100) DEFAULT NULL,
  `nama_wali` varchar(100) DEFAULT NULL,
  `riwayat_alergi` text DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `riwayat_obat` text DEFAULT NULL,
  `pelayanan` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `no_rekam_medis`, `nama_lengkap`, `nama_panggilan`, `tanggal_lahir`, `usia`, `kategori_usia`, `jenis_kelamin`, `nik`, `paspor`, `kebangsaan`, `pekerjaan`, `nama_wali`, `riwayat_alergi`, `riwayat_penyakit`, `riwayat_obat`, `pelayanan`, `created_at`, `updated_at`) VALUES
(35, 'RM1768539532', 'Rofiah Budi Nadia', 'fiah', '2023-01-31', 2, 'Anak', 'P', NULL, NULL, 'Indonesia', 'umum', 'p', 'sdc', 'csd', 'csx', 'Vaksin', '2026-01-16 04:58:52', '2026-01-16 04:58:52'),
(36, 'RM1768547346', 'Rofiah Budi Nadia', 'fiah', '2003-01-07', 23, 'Dewasa', 'P', NULL, NULL, 'Indonesia', 'umum', 'p', '', '', '', 'Antigen', '2026-01-16 07:09:06', '2026-01-16 07:09:06'),
(37, '', 'Pasien Test', NULL, '1990-01-01', NULL, 'Anak', 'L', '1234567890123456', NULL, 'Indonesia', NULL, NULL, NULL, NULL, NULL, '', '2026-01-16 14:13:23', '2026-01-16 14:13:23'),
(38, 'RM202601170001', 'Rofiah Budi Nadia', 'fiah', '2003-01-07', 23, 'Dewasa', 'P', '3314014701030001', '', 'Indonesia', 'umum', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 09:12:38', '2026-01-17 09:12:38'),
(64, 'RM202601170002', 'Rofi\'ah Budi Nadia', 'fiah', '2006-09-11', 19, 'Dewasa', 'P', NULL, '0987654321234', 'Indonesia', 'umum', '', '', '', '', 'Umroh/Haji/Luar Negeri', '2026-01-17 10:21:02', '2026-01-17 10:21:02'),
(65, 'RM202601170003', 'Rofiah Budi Nadia', 'fiah', '2003-01-07', 23, 'Dewasa', 'P', '3314014701030001', NULL, 'Indonesia', 'umum', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 10:21:02', '2026-01-17 10:21:02'),
(66, 'RM202601170004', 'w', 'fiah', '2023-07-06', 2, 'Anak', 'L', NULL, '087654321234567', 'Indonesia', 'umum', 'wr', '', '', '', 'Umroh/Haji/Luar Negeri', '2026-01-17 10:21:02', '2026-01-17 10:21:02'),
(67, 'RM202601170005', 'w', 'fiah', '2023-07-06', 2, 'Anak', 'P', NULL, '087654321234567', 'Indonesia', 'umum', 'wr', '', '', '', 'Umroh/Haji/Luar Negeri', '2026-01-17 10:21:02', '2026-01-17 10:21:02'),
(68, 'RM202601170006', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 12:51:03', '2026-01-17 12:51:03'),
(69, 'RM202601170007', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 13:39:33', '2026-01-17 13:39:33'),
(70, 'RM202601170008', 'a', '', '2026-01-02', 0, 'Anak', 'P', '1234567890987654', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 13:39:33', '2026-01-17 13:39:33'),
(71, 'RM202601170009', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 13:48:30', '2026-01-17 13:48:30'),
(72, 'RM202601170010', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 13:51:44', '2026-01-17 13:51:44'),
(73, 'RM202601170011', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-17 13:51:44', '2026-01-17 13:51:44'),
(75, 'RM202601180001', 'Rofiah Budi Nadia', 'fiah', '2003-01-07', 23, 'Dewasa', 'P', '3314014701030001', NULL, 'Indonesia', 'umum', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-18 04:24:13', '2026-01-18 04:24:13'),
(77, 'RM202601180002', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-18 04:44:39', '2026-01-18 04:44:39'),
(78, 'RM202601180003', 'b', '', '2024-01-30', 1, 'Anak', 'P', '0987654321234567', NULL, 'Indonesia', 'umum', 'd', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-18 04:57:47', '2026-01-18 04:57:47');

-- --------------------------------------------------------

--
-- Table structure for table `patient_addresses`
--

CREATE TABLE `patient_addresses` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `alamat` text NOT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_addresses`
--

INSERT INTO `patient_addresses` (`id`, `patient_id`, `alamat`, `provinsi`, `kota`, `is_primary`) VALUES
(31, 35, 'asddf', NULL, NULL, 1),
(32, 36, 'salam', NULL, NULL, 1),
(33, 38, 'Bandung - Jawa Barat', 'Jambi', 'Sungai Penuh', 1),
(34, 64, 'Bandung - Jawa Barat', 'Jawa Tengah', 'Sragen', 1),
(35, 65, 'Bandung - Jawa Barat', 'Jambi', 'Kerinci', 1),
(36, 66, 'Bandung - Jawa Barat', 'Kepulauan Riau', 'Karimun', 1),
(37, 67, 'Bandung - Jawa Barat', 'Kepulauan Riau', 'Batam', 1),
(38, 68, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(39, 69, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(40, 70, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(41, 71, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(42, 72, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(43, 73, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(45, 75, 'Bandung - Jawa Barat', 'Jambi', 'Kerinci', 1),
(47, 77, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1),
(48, 78, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Cimahi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_emails`
--

CREATE TABLE `patient_emails` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_emails`
--

INSERT INTO `patient_emails` (`id`, `patient_id`, `email`, `is_primary`) VALUES
(32, 35, 'rofiahbudi@gmail.com', 1),
(33, 36, 'rofiahbudi@gmail.com', 1),
(34, 38, 'rofiahbudi@gmail.com', 1),
(35, 64, 'rofiahbudi@gmail.com', 1),
(36, 65, 'rofiahbudi@gmail.com', 1),
(37, 66, 'adsdascc@gmail', 1),
(38, 67, 'adsdascc@gmail', 1),
(39, 68, 'b@gmail.com', 1),
(40, 69, 'b@gmail.com', 1),
(41, 70, 'a@gmail.com', 1),
(42, 71, 'b@gmail.com', 1),
(43, 72, 'b@gmail.com', 1),
(44, 73, 'b@gmail.com', 1),
(46, 75, 'rofiahbudi@gmail.com', 1),
(48, 77, 'b@gmail.com', 1),
(49, 78, 'b@gmail.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_phones`
--

CREATE TABLE `patient_phones` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_phones`
--

INSERT INTO `patient_phones` (`id`, `patient_id`, `phone`, `is_primary`) VALUES
(33, 35, '082225639375', 1),
(34, 36, '082225639375', 1),
(35, 38, '082225639375', 1),
(36, 64, '085876923088', 1),
(37, 65, '082225639375', 1),
(38, 66, '9876543456765', 1),
(39, 67, '9876543456765', 1),
(40, 68, '08654324567', 1),
(41, 69, '08654324567', 1),
(42, 70, '08654324567', 1),
(43, 71, '08654324567', 1),
(44, 72, '08654324567', 1),
(45, 73, '08654324567', 1),
(47, 75, '082225639375', 1),
(49, 77, '08654324567', 1),
(50, 78, '08654324567', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_services`
--

CREATE TABLE `patient_services` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_type` enum('Vaksin','Vitamin','Antigen','PCR','Obat') DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_services`
--

INSERT INTO `patient_services` (`id`, `patient_id`, `service_type`, `service_name`, `created_at`) VALUES
(1, 10, 'Vaksin', 'Adacel (Sanofi)', '2025-12-30 08:02:22'),
(2, 11, 'Vaksin', 'Arexvy (GSK)', '2025-12-30 08:15:39'),
(3, 12, 'Vaksin', 'Pneumovax 23 (MSD)', '2025-12-30 08:42:08'),
(4, 13, 'Vaksin', 'Influvac Tetra (Abbott)', '2025-12-30 09:11:06'),
(5, 33, 'Vitamin', 'Vitamin Badan Bugar', '2026-01-16 04:29:59');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `reservation_code` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled','Rescheduled') DEFAULT 'Pending',
  `total_price` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `notes` text DEFAULT NULL,
  `reminder_h_minus_1` tinyint(1) DEFAULT 0,
  `reminder_h_plus_1` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `max_capacity` int(11) DEFAULT 3,
  `current_booking` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `slot_date`, `slot_time`, `max_capacity`, `current_booking`, `is_active`) VALUES
(1, '2025-12-24', '08:00:00', 3, 0, 1),
(2, '2025-12-24', '08:30:00', 3, 0, 1),
(3, '2025-12-24', '09:00:00', 3, 0, 1),
(4, '2025-12-24', '09:30:00', 3, 0, 1),
(5, '2025-12-24', '10:00:00', 3, 0, 1),
(6, '2025-12-24', '10:30:00', 3, 0, 1),
(7, '2025-12-24', '11:00:00', 3, 0, 1),
(8, '2025-12-24', '11:30:00', 3, 0, 1),
(9, '2025-12-24', '13:00:00', 3, 0, 1),
(10, '2025-12-24', '13:30:00', 3, 0, 1),
(11, '2025-12-24', '14:00:00', 3, 0, 1),
(12, '2025-12-24', '14:30:00', 3, 0, 1),
(13, '2025-12-24', '15:00:00', 3, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_history`
--

CREATE TABLE `vaccination_history` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `vaccination_date` date NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vaccines`
--

CREATE TABLE `vaccines` (
  `id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `min_age` int(11) DEFAULT 0,
  `max_age` int(11) DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccines`
--

INSERT INTO `vaccines` (`id`, `vaccine_name`, `description`, `price`, `stock`, `min_age`, `max_age`, `created_at`) VALUES
(1, 'COVID-19 (Pfizer)', 'Vaksin COVID-19 dari Pfizer-BioNTech', 0.00, 100, 12, 100, '2025-12-24 06:40:17'),
(2, 'COVID-19 (Moderna)', 'Vaksin COVID-19 dari Moderna', 0.00, 100, 12, 100, '2025-12-24 06:40:17'),
(3, 'Influenza', 'Vaksin Flu Musiman', 150000.00, 50, 6, 100, '2025-12-24 06:40:17'),
(4, 'MMR', 'Vaksin Campak, Gondongan, Rubella', 250000.00, 30, 1, 12, '2025-12-24 06:40:17'),
(5, 'Hepatitis B', 'Vaksin Hepatitis B', 200000.00, 40, 0, 100, '2025-12-24 06:40:17'),
(6, 'DPT', 'Vaksin Difteri, Pertusis, Tetanus', 180000.00, 35, 0, 7, '2025-12-24 06:40:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_antrian` (`nomor_antrian`),
  ADD UNIQUE KEY `unique_slot` (`tanggal_booking`,`waktu_booking`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_tanggal` (`tanggal_booking`),
  ADD KEY `idx_waktu` (`waktu_booking`),
  ADD KEY `idx_tanggal_waktu` (`tanggal_booking`,`waktu_booking`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_nomor_antrian` (`nomor_antrian`);

--
-- Indexes for table `booking_services`
--
ALTER TABLE `booking_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking` (`booking_id`);

--
-- Indexes for table `jadwal_klinik`
--
ALTER TABLE `jadwal_klinik`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jadwal_libur`
--
ALTER TABLE `jadwal_libur`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kipi_records`
--
ALTER TABLE `kipi_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_rekam_medis` (`no_rekam_medis`),
  ADD UNIQUE KEY `no_rekam_medis_2` (`no_rekam_medis`);

--
-- Indexes for table `patient_addresses`
--
ALTER TABLE `patient_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patient_emails`
--
ALTER TABLE `patient_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patient_phones`
--
ALTER TABLE `patient_phones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patient_services`
--
ALTER TABLE `patient_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_code` (`reservation_code`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `vaccine_id` (`vaccine_id`),
  ADD KEY `slot_id` (`slot_id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`slot_date`,`slot_time`);

--
-- Indexes for table `vaccination_history`
--
ALTER TABLE `vaccination_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `booking_services`
--
ALTER TABLE `booking_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_klinik`
--
ALTER TABLE `jadwal_klinik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jadwal_libur`
--
ALTER TABLE `jadwal_libur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `kipi_records`
--
ALTER TABLE `kipi_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `patient_addresses`
--
ALTER TABLE `patient_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `patient_emails`
--
ALTER TABLE `patient_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `patient_phones`
--
ALTER TABLE `patient_phones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `patient_services`
--
ALTER TABLE `patient_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `vaccination_history`
--
ALTER TABLE `vaccination_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vaccines`
--
ALTER TABLE `vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_services`
--
ALTER TABLE `booking_services`
  ADD CONSTRAINT `booking_services_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kipi_records`
--
ALTER TABLE `kipi_records`
  ADD CONSTRAINT `kipi_records_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kipi_records_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patient_addresses`
--
ALTER TABLE `patient_addresses`
  ADD CONSTRAINT `patient_addresses_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_emails`
--
ALTER TABLE `patient_emails`
  ADD CONSTRAINT `patient_emails_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_phones`
--
ALTER TABLE `patient_phones`
  ADD CONSTRAINT `patient_phones_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `time_slots` (`id`);

--
-- Constraints for table `vaccination_history`
--
ALTER TABLE `vaccination_history`
  ADD CONSTRAINT `vaccination_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
