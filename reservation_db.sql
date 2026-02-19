-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 19, 2026 at 04:21 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

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
  `id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `patient_id` int NOT NULL,
  `service_type` enum('Home Service','In Clinic') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_antrian` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_booking` date NOT NULL,
  `waktu_booking` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `doctor_id` int DEFAULT NULL,
  `payment_status` enum('unpaid','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `tindakan_selesai` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `parent_id`, `patient_id`, `service_type`, `nomor_antrian`, `tanggal_booking`, `waktu_booking`, `status`, `catatan`, `created_at`, `updated_at`, `doctor_id`, `payment_status`, `tindakan_selesai`) VALUES
(78, NULL, 1, 'In Clinic', '20260120-001', '2026-01-23', '09:30:00', 'confirmed', 'Pendaftaran online', '2026-01-20 09:38:53', '2026-01-26 13:37:47', 1, 'unpaid', 0),
(79, NULL, 2, 'In Clinic', '20260121-001', '2026-01-23', '09:00:00', 'cancelled', 'Pendaftaran online', '2026-01-20 23:23:09', '2026-01-23 21:33:47', NULL, 'unpaid', 0),
(80, NULL, 3, 'In Clinic', '20260122-002', '2026-01-23', '11:00:00', 'confirmed', 'Pendaftaran online', '2026-01-22 15:53:09', '2026-02-05 20:13:16', NULL, 'unpaid', 1),
(81, NULL, 4, 'In Clinic', '20260122-003', '2026-01-23', '11:30:00', 'confirmed', 'Pendaftaran online', '2026-01-22 17:17:05', '2026-02-05 23:47:40', NULL, 'unpaid', 1),
(82, NULL, 5, 'In Clinic', '20260126-001', '2026-01-26', '10:45:00', 'completed', 'Pendaftaran online', '2026-01-26 09:42:15', '2026-01-28 00:26:50', NULL, 'paid', 1),
(83, NULL, 1, 'In Clinic', '20260127-001', '2026-01-27', '09:15:00', 'confirmed', 'Pendaftaran online', '2026-01-26 09:56:30', '2026-01-27 12:45:38', NULL, 'unpaid', 1),
(84, NULL, 6, 'In Clinic', '20260126-002', '2026-01-26', '14:30:00', 'completed', 'Pendaftaran online', '2026-01-26 09:56:30', '2026-01-29 02:17:28', NULL, 'paid', 1),
(85, NULL, 2, 'In Clinic', '20260126-003', '2026-01-26', '11:15:00', 'cancelled', 'Pendaftaran online', '2026-01-26 16:04:53', '2026-01-26 17:15:52', NULL, 'unpaid', 0),
(86, NULL, 1, 'In Clinic', '20260202-001', '2026-02-02', '09:00:00', 'completed', 'Pendaftaran online', '2026-02-02 14:47:44', '2026-02-05 19:54:53', NULL, 'paid', 1),
(87, NULL, 1, 'In Clinic', '20260218-001', '2026-02-18', '09:30:00', 'completed', 'Pendaftaran online', '2026-02-02 16:43:28', '2026-02-18 17:23:03', NULL, 'paid', 1),
(88, NULL, 2, 'In Clinic', '20260211-001', '2026-02-11', '09:00:00', 'completed', 'Pendaftaran online', '2026-02-02 16:43:28', '2026-02-19 13:44:42', NULL, 'paid', 1),
(89, NULL, 2, 'In Clinic', '20260218-002', '2026-02-18', '09:45:00', 'completed', 'Pendaftaran online', '2026-02-02 16:49:15', '2026-02-18 19:11:35', NULL, 'paid', 1),
(90, NULL, 7, 'In Clinic', '20260204-001', '2026-02-04', '09:15:00', 'cancelled', 'Pendaftaran online', '2026-02-02 16:58:32', '2026-02-04 11:32:43', NULL, 'unpaid', 0),
(91, NULL, 8, 'In Clinic', '20260203-001', '2026-02-03', '09:30:00', 'completed', 'Pendaftaran online', '2026-02-02 20:03:33', '2026-02-05 19:57:09', NULL, 'paid', 1),
(99, NULL, 2, 'In Clinic', '20260204-002', '2026-02-04', '09:30:00', 'confirmed', 'Pendaftaran online', '2026-02-03 16:34:16', '2026-02-05 16:11:53', NULL, 'paid', 1),
(101, 99, 14, 'In Clinic', '20260204-002', '2026-02-04', '09:30:00', 'pending', 'Pendaftaran online', '2026-02-03 16:34:16', '2026-02-05 16:11:53', NULL, 'paid', 1),
(102, NULL, 1, 'In Clinic', '20260204-003', '2026-02-04', '09:45:00', 'confirmed', 'Pendaftaran online', '2026-02-03 17:22:18', '2026-02-05 18:53:16', NULL, 'paid', 1),
(103, 102, 15, 'In Clinic', '20260204-003', '2026-02-04', '09:45:00', 'pending', 'Pendaftaran online', '2026-02-03 17:22:18', '2026-02-05 18:53:16', NULL, 'paid', 1),
(104, 102, 16, 'In Clinic', '20260204-003', '2026-02-04', '09:45:00', 'pending', 'Pendaftaran online', '2026-02-03 17:22:18', '2026-02-05 18:53:16', NULL, 'paid', 1),
(105, NULL, 17, 'In Clinic', '20260204-004', '2026-02-04', '14:45:00', 'completed', 'Pendaftaran online', '2026-02-04 13:50:51', '2026-02-05 23:37:53', NULL, 'paid', 1),
(106, 105, 2, 'In Clinic', '20260204-004', '2026-02-04', '14:45:00', 'completed', 'Pendaftaran online', '2026-02-04 13:50:51', '2026-02-05 23:37:53', NULL, 'paid', 1),
(107, 105, 18, 'In Clinic', '20260204-004', '2026-02-04', '14:45:00', 'completed', 'Pendaftaran online', '2026-02-04 13:50:51', '2026-02-05 23:37:53', NULL, 'paid', 1),
(108, NULL, 19, 'In Clinic', '20260204-005', '2026-02-04', '10:15:00', 'completed', 'Pendaftaran online', '2026-02-04 14:33:59', '2026-02-05 20:17:11', NULL, 'paid', 1),
(109, 108, 20, 'In Clinic', '20260204-005', '2026-02-04', '10:15:00', 'completed', 'Pendaftaran online', '2026-02-04 14:33:59', '2026-02-05 20:17:11', NULL, 'paid', 1),
(110, NULL, 21, 'In Clinic', '20260206-001', '2026-02-06', '09:00:00', 'completed', 'Pendaftaran online', '2026-02-05 20:34:35', '2026-02-05 20:36:25', NULL, 'paid', 1),
(111, 110, 22, 'In Clinic', '20260206-001', '2026-02-06', '09:00:00', 'completed', 'Pendaftaran online', '2026-02-05 20:34:35', '2026-02-05 20:36:25', NULL, 'paid', 1),
(112, NULL, 23, 'In Clinic', '20260206-002', '2026-02-06', '09:15:00', 'completed', 'Pendaftaran online', '2026-02-05 20:51:06', '2026-02-05 20:51:58', NULL, 'paid', 1),
(113, NULL, 24, 'In Clinic', '20260206-003', '2026-02-06', '09:30:00', 'completed', 'Pendaftaran online', '2026-02-05 23:49:47', '2026-02-05 23:50:45', NULL, 'paid', 1),
(114, NULL, 25, 'In Clinic', '20260207-001', '2026-02-07', '09:00:00', 'completed', 'Pendaftaran online', '2026-02-06 00:00:25', '2026-02-06 00:01:56', NULL, 'paid', 1),
(115, NULL, 26, 'In Clinic', '20260207-002', '2026-02-07', '09:30:00', 'completed', 'Pendaftaran online', '2026-02-06 00:15:57', '2026-02-06 00:46:28', NULL, 'paid', 1),
(116, NULL, 1, 'In Clinic', '20260207-003', '2026-02-07', '09:00:00', 'confirmed', 'Pendaftaran online', '2026-02-06 18:51:17', '2026-02-06 18:52:59', NULL, 'unpaid', 0),
(117, 116, 27, 'In Clinic', '20260207-003', '2026-02-07', '09:00:00', 'pending', 'Pendaftaran online', '2026-02-06 18:51:17', '2026-02-06 18:51:17', NULL, 'unpaid', 0),
(118, NULL, 1, 'In Clinic', '20260213-001', '2026-02-13', '09:00:00', 'completed', 'Pendaftaran online', '2026-02-12 23:28:47', '2026-02-19 13:33:08', NULL, 'paid', 1),
(119, NULL, 28, 'In Clinic', '20260219-001', '2026-02-19', '09:30:00', 'confirmed', 'Pendaftaran online', '2026-02-18 19:09:46', '2026-02-19 14:58:06', NULL, 'unpaid', 1),
(120, 119, 29, 'In Clinic', '20260219-001', '2026-02-19', '09:30:00', 'pending', 'Pendaftaran online', '2026-02-18 19:09:46', '2026-02-18 19:09:46', NULL, 'unpaid', 0),
(121, NULL, 30, 'In Clinic', '20260219-002', '2026-02-19', '09:45:00', 'completed', 'Pendaftaran online', '2026-02-18 19:52:00', '2026-02-18 20:01:05', NULL, 'paid', 1),
(122, 121, 31, 'In Clinic', '20260219-002', '2026-02-19', '09:45:00', 'completed', 'Pendaftaran online', '2026-02-18 19:52:00', '2026-02-18 20:01:05', NULL, 'paid', 1);

-- --------------------------------------------------------

--
-- Table structure for table `booking_services`
--

CREATE TABLE `booking_services` (
  `id` int NOT NULL,
  `parent_booking_id` int DEFAULT NULL,
  `booking_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `nama_layanan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `service_id` int DEFAULT NULL,
  `harga` int DEFAULT '0',
  `diskon` int DEFAULT '0',
  `diskon_tipe` enum('persen','nilai') COLLATE utf8mb4_unicode_ci DEFAULT 'nilai',
  `total` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_services`
--

INSERT INTO `booking_services` (`id`, `parent_booking_id`, `booking_id`, `patient_id`, `nama_layanan`, `created_at`, `service_id`, `harga`, `diskon`, `diskon_tipe`, `total`) VALUES
(18, 78, 78, 1, 'Adacel (Sanofi)', '2026-01-20 09:38:53', NULL, 0, 0, 'nilai', 0),
(19, 79, 79, 2, 'Campak (Biofarma)', '2026-01-20 23:23:09', NULL, 0, 0, 'nilai', 0),
(20, 79, 79, 2, 'Swab Antigen COVID-19', '2026-01-20 23:23:09', NULL, 0, 0, 'nilai', 0),
(21, 80, 80, 3, 'Vitamin Badan Bugar', '2026-01-22 15:53:09', NULL, 0, 0, 'nilai', 0),
(22, 80, 80, 3, 'Vitamin D3', '2026-01-22 15:53:09', NULL, 0, 0, 'nilai', 0),
(23, 81, 81, 4, 'Pantoprazole 40 mg Vial', '2026-01-22 17:17:05', NULL, 0, 0, 'persen', 0),
(24, 82, 82, 5, 'Pantoprazole 40 mg Vial', '2026-01-26 09:42:15', 73, 120000, 20400, 'persen', 99600),
(25, 83, 83, 1, 'Skrining Kesehatan Awal', '2026-01-26 09:56:30', 17, 35000, 0, 'persen', 35000),
(26, 84, 84, 6, 'Paracetamol 1 g Fl', '2026-01-26 09:56:30', 74, 80000, 18400, 'persen', 61600),
(28, 85, 85, 2, 'Adacel (Sanofi)', '2026-01-26 16:04:53', 1, 350000, 0, 'nilai', 0),
(29, 86, 86, 1, 'Pantoprazole 40 mg Vial', '2026-02-02 14:47:44', 73, 120000, 0, 'nilai', 0),
(30, 87, 87, 1, 'Vaksinasi HPV', '2026-02-02 16:43:28', 7, 350000, 0, 'persen', 350000),
(32, 89, 89, 2, 'Adacel (Sanofi)', '2026-02-02 16:49:15', 1, 350000, 50000, 'nilai', 0),
(33, 89, 89, 2, 'Fluarix Tetra (GSK)', '2026-02-02 16:49:15', 14, 450000, 0, 'nilai', 0),
(34, 90, 90, 7, 'Medical Check Up Lengkap', '2026-02-02 16:58:32', 54, 1500000, 0, 'nilai', 0),
(35, 91, 91, 8, 'Konsultasi Dokter Umum', '2026-02-02 20:03:33', 80, 75000, 0, 'nilai', 0),
(36, 99, 99, 2, 'Konsultasi Dokter Umum', '2026-02-03 16:34:16', 80, 75000, 0, 'nilai', 0),
(37, 99, 101, 14, 'Suntik Vitamin C', '2026-02-03 16:34:16', 61, 200000, 0, 'nilai', 0),
(38, 102, 102, 1, 'Swab Antigen COVID-19', '2026-02-03 17:22:18', 76, 100000, 0, 'nilai', 0),
(39, 102, 103, 15, 'Paracetamol 1 g Fl', '2026-02-03 17:22:18', 74, 80000, 0, 'nilai', 0),
(40, 102, 103, 15, 'Swab PCR COVID-19', '2026-02-03 17:22:18', 77, 350000, 0, 'nilai', 0),
(41, 102, 104, 16, 'Hepatitis B Dewasa (Biofarma)', '2026-02-03 17:22:18', 20, 200000, 0, 'nilai', 0),
(42, 102, 104, 16, 'Vecon Adult (Biofarma)', '2026-02-03 17:22:18', 48, 300000, 0, 'nilai', 0),
(43, 105, 105, 17, 'Tuberculin PPD RT 23 SSI', '2026-02-04 13:50:51', 75, 250000, 0, 'nilai', 0),
(44, 105, 106, 2, 'Adacel (Sanofi)', '2026-02-04 13:50:51', 1, 350000, 0, 'nilai', 0),
(45, 105, 107, 18, 'bOPV Polio (Biofarma)', '2026-02-04 13:50:51', 7, 120000, 0, 'nilai', 0),
(46, 108, 108, 19, 'Suntik Vitamin C', '2026-02-04 14:33:59', 61, 200000, 0, 'nilai', 0),
(47, 108, 109, 20, 'BCG (Biofarma)', '2026-02-04 14:33:59', 5, 150000, 0, 'nilai', 0),
(48, NULL, 110, 21, 'Suntik Vitamin C', '2026-02-05 20:34:35', 61, 200000, 0, 'nilai', 0),
(49, NULL, 111, 22, 'Swab PCR COVID-19', '2026-02-05 20:34:35', 77, 350000, 0, 'nilai', 0),
(50, NULL, 112, 23, 'Swab Antigen COVID-19', '2026-02-05 20:51:06', 76, 100000, 0, 'nilai', 0),
(51, NULL, 113, 24, 'Arexvy (GSK)', '2026-02-05 23:49:47', 2, 750000, 525000, 'persen', 0),
(52, NULL, 114, 25, 'Paracetamol 1 g Fl', '2026-02-06 00:00:25', 74, 80000, 0, 'nilai', 0),
(53, NULL, 115, 26, 'Suntik Vitamin C', '2026-02-06 00:15:57', 61, 200000, 42000, 'persen', 0),
(54, NULL, 116, 1, 'Suntik Vitamin C', '2026-02-06 18:51:17', 61, 200000, 0, 'nilai', 0),
(55, NULL, 117, 27, 'Paracetamol 1 g Fl', '2026-02-06 18:51:17', 74, 80000, 0, 'nilai', 0),
(56, NULL, 118, 1, 'Paket Vaksinasi Influenza 2 Dosis', '2026-02-12 23:28:47', 5, 450000, 0, 'persen', 450000),
(57, NULL, 119, 28, 'Medical Check Up', '2026-02-18 19:09:46', 11, 500000, 0, 'nilai', 0),
(58, NULL, 120, 29, 'Medical Check Up', '2026-02-18 19:09:46', 11, 500000, 0, 'nilai', 0),
(59, NULL, 121, 30, 'Infus Vitamin C', '2026-02-18 19:52:00', 9, 200000, 0, 'persen', 200000),
(60, NULL, 122, 31, 'Infus Vitamin C', '2026-02-18 19:52:00', 9, 200000, 0, 'nilai', 200000),
(61, NULL, 121, 30, 'Medical Check Up', '2026-02-18 19:59:56', 11, 500000, 0, 'persen', 500000),
(62, NULL, 88, 2, 'Medical Check Up', '2026-02-19 13:44:23', 11, 500000, 50000, 'persen', 450000);

-- --------------------------------------------------------

--
-- Table structure for table `booking_staff`
--

CREATE TABLE `booking_staff` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_staff`
--

INSERT INTO `booking_staff` (`id`, `booking_id`, `staff_id`, `created_at`) VALUES
(13, 78, 3, '2026-01-23 14:34:10'),
(14, 80, 1, '2026-01-23 14:36:03'),
(19, 85, 2, '2026-01-26 09:08:23'),
(20, 83, 1, '2026-01-26 09:59:41'),
(21, 84, 1, '2026-01-26 11:32:06'),
(22, 82, 2, '2026-01-26 18:11:53'),
(23, 99, 1, '2026-02-04 12:04:37'),
(24, 102, 1, '2026-02-05 09:51:43'),
(25, 86, 2, '2026-02-05 12:53:55'),
(26, 91, 3, '2026-02-05 12:56:02'),
(27, 108, 2, '2026-02-05 13:13:40'),
(28, 110, 2, '2026-02-05 13:34:44'),
(29, 112, 2, '2026-02-05 13:51:27'),
(30, 105, 2, '2026-02-05 16:36:53'),
(31, 81, 2, '2026-02-05 16:47:35'),
(32, 113, 2, '2026-02-05 16:50:13'),
(34, 114, 3, '2026-02-05 17:00:42'),
(35, 115, 1, '2026-02-05 17:16:05'),
(36, 116, 1, '2026-02-06 11:52:59'),
(37, 87, 1, '2026-02-18 10:20:49'),
(38, 89, 2, '2026-02-18 12:10:26'),
(39, 121, 2, '2026-02-18 13:00:04'),
(40, 88, 3, '2026-02-19 06:31:53'),
(41, 118, 1, '2026-02-19 06:32:37'),
(42, 119, 1, '2026-02-19 07:57:59');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_khusus`
--

CREATE TABLE `jadwal_khusus` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL COMMENT 'Tanggal individual (hasil generate dari rentang)',
  `tanggal_mulai` date NOT NULL COMMENT 'Tanggal mulai rentang',
  `tanggal_selesai` date NOT NULL COMMENT 'Tanggal selesai rentang',
  `jam_buka` time NOT NULL,
  `jam_tutup` time NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `status` enum('buka','tutup') NOT NULL DEFAULT 'buka',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jadwal_khusus`
--

INSERT INTO `jadwal_khusus` (`id`, `tanggal`, `tanggal_mulai`, `tanggal_selesai`, `jam_buka`, `jam_tutup`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(2, '2026-02-11', '2026-02-11', '2026-02-11', '09:00:00', '17:00:00', '', 'tutup', '2026-02-09 15:48:51', '2026-02-09 15:48:51');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_klinik`
--

CREATE TABLE `jadwal_klinik` (
  `id` int NOT NULL,
  `hari_week` int DEFAULT NULL COMMENT '1=Minggu, 2=Senin, ..., 7=Sabtu',
  `jam_buka` time DEFAULT NULL,
  `jam_tutup` time DEFAULT NULL,
  `status` enum('buka','tutup') COLLATE utf8mb4_general_ci DEFAULT 'buka'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_klinik`
--

INSERT INTO `jadwal_klinik` (`id`, `hari_week`, `jam_buka`, `jam_tutup`, `status`) VALUES
(7, 2, '09:00:00', '16:30:00', 'buka'),
(8, 3, '09:00:00', '16:30:00', 'buka'),
(9, 4, '09:00:00', '16:30:00', 'buka'),
(10, 5, '09:00:00', '16:30:00', 'buka'),
(11, 6, '09:00:00', '16:30:00', 'buka'),
(12, 7, '09:00:00', '16:30:00', 'buka');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_libur`
--

CREATE TABLE `jadwal_libur` (
  `id` int NOT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis` enum('nasional','khusus','minggu') COLLATE utf8mb4_general_ci DEFAULT 'nasional'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_libur`
--

INSERT INTO `jadwal_libur` (`id`, `tanggal`, `keterangan`, `jenis`) VALUES
(43, '2024-03-11', 'Hari Raya Nyepi', 'nasional'),
(44, '2024-04-10', 'Idul Fitri 1445 H', 'nasional'),
(45, '2024-05-01', 'Hari Buruh Internasional', 'nasional'),
(46, '2024-05-09', 'Kenaikan Isa Almasih', 'nasional'),
(47, '2024-06-01', 'Hari Lahir Pancasila', 'nasional'),
(48, '2024-08-17', 'Hari Kemerdekaan RI', 'nasional'),
(49, '2026-01-01', 'Tahun Baru Masehi', 'nasional'),
(50, '2026-01-16', 'Isra Mi’raj Nabi Muhammad SAW', 'nasional'),
(51, '2026-02-17', 'Tahun Baru Imlek 2577 Kongzili', 'nasional'),
(52, '2026-03-19', 'Hari Suci Nyepi', 'nasional'),
(53, '2026-03-21', 'Hari Raya Idul Fitri (1)', 'nasional'),
(54, '2026-03-22', 'Hari Raya Idul Fitri (2)', 'nasional'),
(55, '2026-04-03', 'Wafat Yesus Kristus', 'nasional'),
(56, '2026-04-05', 'Hari Kebangkitan Yesus Kristus (Paskah)', 'nasional'),
(57, '2026-05-01', 'Hari Buruh Internasional', 'nasional'),
(58, '2026-05-14', 'Kenaikan Yesus Kristus', 'nasional'),
(59, '2026-05-27', 'Hari Raya Idul Adha 1447 H', 'nasional'),
(60, '2026-05-31', 'Hari Raya Waisak 2570 BE', 'nasional'),
(61, '2026-06-01', 'Hari Lahir Pancasila', 'nasional'),
(62, '2026-06-16', '1 Muharram 1448 H', 'nasional'),
(63, '2026-08-17', 'Hari Proklamasi Kemerdekaan RI', 'nasional'),
(64, '2026-08-25', 'Maulid Nabi Muhammad SAW', 'nasional'),
(65, '2026-12-25', 'Kelahiran Yesus Kristus (Natal)', 'nasional');

-- --------------------------------------------------------

--
-- Table structure for table `kipi_records`
--

CREATE TABLE `kipi_records` (
  `id` int NOT NULL,
  `reservation_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `kipi_date` date NOT NULL,
  `symptoms` text COLLATE utf8mb4_general_ci,
  `severity` enum('Ringan','Sedang','Berat') COLLATE utf8mb4_general_ci DEFAULT 'Ringan',
  `action_taken` text COLLATE utf8mb4_general_ci,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_actions`
--

CREATE TABLE `medical_actions` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `layanan` text COLLATE utf8mb4_general_ci,
  `tanggal_vaksinasi` date DEFAULT NULL,
  `jenis_vaksin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `batch_vaksin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expired_vaksin` date DEFAULT NULL,
  `kedatangan_ke` int DEFAULT NULL,
  `kedatangan_selanjutnya` int DEFAULT NULL,
  `status` enum('Aktif','Selesai') COLLATE utf8mb4_general_ci DEFAULT 'Aktif',
  `anamnesis` text COLLATE utf8mb4_general_ci,
  `pemeriksaan_fisik` text COLLATE utf8mb4_general_ci,
  `diagnosis` text COLLATE utf8mb4_general_ci,
  `tatalaksana` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_letters`
--

CREATE TABLE `medical_letters` (
  `id` int NOT NULL,
  `action_id` int NOT NULL,
  `jenis` enum('sehat','sakit','vaksin') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `dokter_id` int DEFAULT NULL,
  `posisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `reservation_id` int DEFAULT NULL,
  `record_date` datetime NOT NULL,
  `keluhan` text COLLATE utf8mb4_general_ci,
  `diagnosis` text COLLATE utf8mb4_general_ci,
  `treatment` text COLLATE utf8mb4_general_ci,
  `notes` text COLLATE utf8mb4_general_ci,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int NOT NULL,
  `no_rekam_medis` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_panggilan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  `usia` int DEFAULT NULL,
  `kategori_usia` enum('Anak','Dewasa') COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paspor` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kebangsaan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Indonesia',
  `pekerjaan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_wali` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `riwayat_alergi` text COLLATE utf8mb4_general_ci,
  `riwayat_penyakit` text COLLATE utf8mb4_general_ci,
  `riwayat_obat` text COLLATE utf8mb4_general_ci,
  `pelayanan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `no_rekam_medis`, `nama_lengkap`, `nama_panggilan`, `tanggal_lahir`, `usia`, `kategori_usia`, `jenis_kelamin`, `nik`, `paspor`, `kebangsaan`, `pekerjaan`, `nama_wali`, `riwayat_alergi`, `riwayat_penyakit`, `riwayat_obat`, `pelayanan`, `created_at`, `updated_at`) VALUES
(1, 'RM202601200001', 'Rofi\'ah Budi Nadia', 'fiah', '2003-01-07', 23, 'Dewasa', 'P', '3314014701030001', NULL, 'Indonesia', 'umum', '', 'gaada', 'gerd', NULL, 'In Clinic', '2026-01-20 02:38:53', '2026-02-05 12:02:33'),
(2, 'RM202601200002', 'Leo', '', '2000-04-21', 26, 'Dewasa', 'L', '3314123456789098', NULL, 'namibiaa', 'karyawan swasta', '', 'sifud', 'sakit kepala', 'alkohol', 'In Clinic', '2026-01-20 16:23:09', '2026-02-12 15:58:59'),
(3, 'RM202601220001', 'Dillon', '', '1999-01-28', 26, 'Dewasa', 'L', '3314567654890765', NULL, 'Indonesia', 'karyawan swasta', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-22 08:53:09', '2026-01-22 08:53:09'),
(4, 'RM202601220002', 'Joya', '', '2002-06-19', 23, 'Dewasa', 'P', '3314565428790654', NULL, 'Indonesia', 'nganggur', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-22 10:17:05', '2026-01-22 10:17:05'),
(5, 'RM202601260001', 'paul klein', 'ul', '1988-04-30', 37, 'Dewasa', 'L', '3314567238769076', NULL, 'Amerika', 'singer', '', 'ga', 'gd', 'gd', 'Vaksinasi Umum/Infus Vitamin', '2026-01-26 02:42:15', '2026-01-26 02:42:15'),
(6, 'RM202601260002', 'dorami', 'do', '2021-07-07', 4, 'Anak', 'P', '3314562765438976', NULL, 'Indonesia', 'pelajar', 'p', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-26 02:56:30', '2026-01-26 02:56:30'),
(7, 'RM202602020001', 'lorem', '', '1997-11-13', 28, 'Dewasa', 'L', '3314323456547689', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-02 09:58:32', '2026-02-02 09:58:32'),
(8, 'RM202602020002', 'Eloise', '', '2008-11-19', 17, 'Anak', 'P', '3312324354657687', NULL, 'Indonesia', '', 'violet', '-', '-', '-', 'Vaksinasi Umum/Infus Vitamin', '2026-02-02 13:03:33', '2026-02-02 13:03:33'),
(13, 'RM202602030001', 'Leo', '', '2000-04-21', 25, 'Dewasa', 'L', NULL, '0987656787654321', 'Indonesia', 'karyawan swasta', '', 'sifud', 'sakit kepala', 'alkohol', 'Umroh/Haji/Luar Negeri', '2026-02-03 09:34:16', '2026-02-03 09:34:16'),
(14, 'RM202602030002', 'weni', '', '1995-06-13', 31, 'Dewasa', 'P', '0989878765436782', NULL, 'Indonesia', NULL, '', NULL, NULL, NULL, 'In Clinic', '2026-02-03 09:34:16', '2026-02-04 11:17:26'),
(15, 'RM202602030003', 'Joe', '', '2014-03-06', 11, 'Anak', 'L', '0987654323456765', NULL, 'Indonesia', '', 'p', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-03 10:22:18', '2026-02-03 10:22:18'),
(16, 'RM202602030004', 'Tiarae', '', '2002-02-28', 23, 'Dewasa', 'P', '1234256347652879', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-03 10:22:18', '2026-02-03 10:22:18'),
(17, 'RM202602040001', 'Marcellino', '', '2004-07-08', 21, 'Dewasa', 'L', '1234567898765678', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-04 06:50:51', '2026-02-04 06:50:51'),
(18, 'RM202602040002', 'Gilbert', '', '2003-07-10', 22, 'Dewasa', 'L', '9876543234567899', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-04 06:50:51', '2026-02-04 06:50:51'),
(19, 'RM202602040003', 'Febrina N', '', '2004-03-03', 21, 'Dewasa', 'P', '1234543245678987', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-04 07:33:59', '2026-02-04 07:33:59'),
(20, 'RM202602040004', 'Riza P', '', '2003-03-06', 22, 'Dewasa', 'L', '1233432154367654', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-04 07:33:59', '2026-02-04 07:33:59'),
(21, 'RM202602050001', 'ava', '', '2008-02-05', 18, 'Dewasa', 'P', NULL, '0987654321234567', 'Indonesia', 'umum', '', '', '', '', 'Umroh/Haji/Luar Negeri', '2026-02-05 13:34:35', '2026-02-18 07:26:26'),
(22, 'RM202602050002', 'sociolla', '', '2012-03-07', 13, 'Anak', 'P', NULL, '1234567876545671', 'Indonesia', '', 'r', '', '', '', 'Umroh/Haji/Luar Negeri', '2026-02-05 13:34:35', '2026-02-05 13:34:35'),
(23, 'RM202602050003', 'jean', '', '2004-02-04', 22, 'Dewasa', 'P', '8765432345678765', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-05 13:51:06', '2026-02-05 13:51:06'),
(24, 'RM202602050004', 'benita', '', '2006-01-31', 20, 'Dewasa', 'P', '7654323456787654', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-05 16:49:47', '2026-02-05 16:49:47'),
(25, 'RM202602050005', 'siti', '', '2002-03-06', 23, 'Dewasa', 'P', '3312324323435674', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-05 17:00:25', '2026-02-05 17:00:25'),
(26, 'RM202602050006', 'mita', '', '2003-02-27', 22, 'Dewasa', 'P', '0987878787676765', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-05 17:15:57', '2026-02-05 17:15:57'),
(27, 'RM202602060001', 'sociolla', '', '2012-03-07', 13, 'Anak', 'P', '3314546565678769', NULL, 'Indonesia', '', 'p', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-06 11:51:17', '2026-02-06 11:51:17'),
(28, 'RM202602180001', 'Alexander Wijaya', '', '2002-09-11', 23, 'Dewasa', 'L', '3278907654324567', NULL, 'Indonesia', 'singer', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-18 12:09:46', '2026-02-19 14:59:26'),
(29, 'RM202602180002', 'Jessica Nathalia', '', '2003-07-05', 22, 'Dewasa', 'P', '3273015070300087', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-18 12:09:46', '2026-02-18 12:09:46'),
(30, 'RM202602180003', 'Keanu Andromeda', '', '2001-07-11', 25, 'Dewasa', 'L', '3273011701500107', NULL, 'Indonesia', 'karyawan swasta', '', 'Alergi dingin', NULL, 'Vit C', 'In Clinic', '2026-02-18 12:52:00', '2026-02-18 12:59:56'),
(31, 'RM202602180004', 'Alya Syakira', '', '2001-10-24', 24, 'Dewasa', 'P', '3276548796510987', NULL, 'Indonesia', '', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-02-18 12:52:00', '2026-02-18 12:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `patient_addresses`
--

CREATE TABLE `patient_addresses` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kota` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_addresses`
--

INSERT INTO `patient_addresses` (`id`, `patient_id`, `alamat`, `provinsi`, `kota`, `is_primary`) VALUES
(74, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(75, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(76, 3, 'cribon', 'Jawa Barat', 'Cirebon', 1),
(77, 4, 'seattle', 'DKI Jakarta', 'Jakarta Utara', 1),
(78, 5, 'los angeles', 'Jawa Barat', 'Cimahi', 1),
(79, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(81, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(82, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(83, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(84, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(85, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(86, 7, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Pangandaran', 1),
(87, 8, 'jl. london', 'Riau', 'Rokan Hulu', 1),
(96, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(97, 13, 'Bandung - Jawa Barat', 'Sumatera Barat', 'Solok Selatan', 1),
(98, 14, 'Bandung - Jawa Barat', 'Aceh', 'Aceh Barat Daya', 1),
(99, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(100, 15, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(101, 16, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(102, 17, 'Jl. Umayah 1', 'Jawa Timur', 'Nganjuk', 1),
(103, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(104, 18, 'Jl. Umayah 1', 'Jawa Timur', 'Nganjuk', 1),
(105, 19, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Subang', 1),
(106, 20, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Subang', 1),
(107, 21, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Tengah', 'Kudus', 1),
(108, 22, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Tengah', 'Kudus', 1),
(109, 23, 'Kost Adinda Recidence, Jalan Umayah I, Rt 2/Rw 15, Citeureup (Kosan Warna Oren), KAB. BANDUNG, DAYEUHKOLOT, JAWA BARAT, ID, 40257', 'Jawa Barat', 'Indramayu', 1),
(110, 24, 'Kost Adinda Recidence, Jalan Umayah I, Rt 2/Rw 15, Citeureup (Kosan Warna Oren), KAB. BANDUNG, DAYEUHKOLOT, JAWA BARAT, ID, 40257', 'Jawa Barat', 'Pangandaran', 1),
(111, 25, 'Jl. Telekomunikasi No. 1, Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot', 'Jawa Barat', 'Depok', 1),
(112, 26, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Tengah', 'Magelang', 1),
(113, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(114, 27, 'Kost Adinda Recidence, Jalan Umayah I, Rt 2/Rw 15, Citeureup (Kosan Warna Oren), KAB. BANDUNG, DAYEUHKOLOT, JAWA BARAT, ID, 40257', 'Jawa Barat', 'Purwakarta', 1),
(118, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Barat', 'Bandung', 1),
(119, 28, 'Apartemen Southgate, Tower C Lt. 21, jl. Raya Pasar Minggu', 'DKI Jakarta', 'Jakarta Selatan', 1),
(120, 29, 'Apartemen Southgate, Tower C Lt. 21, jl. Raya Pasar Minggu', 'DKI Jakarta', 'Jakarta Selatan', 1),
(121, 30, 'Kawasan SCBD, Jl, Jendr. Sudirman Kav 52-54', 'DKI Jakarta', 'Jakarta Selatan', 1),
(122, 31, 'Kawasan SCBD, Jl, Jendr. Sudirman Kav 52-54', 'DKI Jakarta', 'Jakarta Selatan', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_emails`
--

CREATE TABLE `patient_emails` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_emails`
--

INSERT INTO `patient_emails` (`id`, `patient_id`, `email`, `is_primary`) VALUES
(78, 3, 'dillon@gmail.com', 1),
(79, 4, 'joya@gmail.com', 1),
(80, 5, 'paul@gmail.com', 1),
(81, 1, 'rofiahbudi@gmail.com', 1),
(82, 6, 'adsdascc@gmail', 1),
(83, 2, 'leo@gmail.com', 1),
(88, 7, 'lorem@gmail.com', 1),
(89, 8, 'eloise@gmail.com', 1),
(99, 13, 'rofiahbudi@gmail.com', 1),
(100, 14, 'rofiahbudi@gmail.com', 1),
(104, 15, 'rofiahbudi@gmail.com', 1),
(105, 16, 'rofiahbudi@gmail.com', 1),
(106, 17, 'marcel@gmail.com', 1),
(108, 18, 'marcel@gmail.com', 1),
(109, 19, 'feb@gmail.com', 1),
(110, 20, 'feb@gmail.com', 1),
(111, 21, 'ava@gmail.com', 1),
(112, 22, 'ava@gmail.com', 1),
(113, 23, 'jean@gmail.com', 1),
(114, 24, 'ben@gmail.com', 1),
(115, 25, 'siti@gmail.com', 1),
(116, 26, 'mita@gmail.com', 1),
(118, 27, 'rofiahbudi@gmail.com', 1),
(127, 28, 'kevin.alexander@gmail.com', 1),
(128, 29, 'kevin.alexander@gmail.com', 1),
(129, 30, 'ke.anuanro@gmail.com', 1),
(130, 31, 'ke.anuanro@gmail.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_phones`
--

CREATE TABLE `patient_phones` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_phones`
--

INSERT INTO `patient_phones` (`id`, `patient_id`, `phone`, `is_primary`) VALUES
(78, 2, '085787652345', 1),
(79, 3, '087654323456', 1),
(80, 4, '087698764536', 1),
(81, 5, '087654323456', 1),
(83, 6, '9876543456765', 1),
(85, 1, '085876923088', 1),
(89, 7, '098767687609', 1),
(90, 8, '098765457890', 1),
(100, 13, '085876923088', 1),
(101, 14, '085876923088', 1),
(105, 15, '085876923088', 1),
(106, 16, '085876923088', 1),
(107, 17, '098765678765', 1),
(109, 18, '098765678765', 1),
(110, 19, '087987898765', 1),
(111, 20, '087987898765', 1),
(112, 21, '065478765432', 1),
(113, 22, '065478765432', 1),
(114, 23, '087654345676', 1),
(115, 24, '087987676545', 1),
(116, 25, '098765666556', 1),
(117, 26, '090909090909', 1),
(119, 27, '085876923088', 1),
(128, 21, '098989898767', 0),
(129, 28, '082122334455', 1),
(130, 29, '082122334455', 1),
(131, 30, '087654567876', 1),
(132, 31, '087654567876', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_services`
--

CREATE TABLE `patient_services` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `service_type` enum('Vaksin','Vitamin','Antigen','PCR','Obat') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `metode` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `diskon` decimal(12,2) DEFAULT NULL,
  `diskon_tipe` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `amount_paid` decimal(12,2) DEFAULT NULL,
  `remaining_balance` decimal(12,2) DEFAULT NULL,
  `payment_type` enum('full','partial','installment') COLLATE utf8mb4_general_ci DEFAULT 'full',
  `jatuh_tempo` date DEFAULT NULL,
  `status` enum('unpaid','paid','partial','cancelled','refunded') COLLATE utf8mb4_general_ci DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `metode`, `subtotal`, `diskon`, `diskon_tipe`, `total`, `amount_paid`, `remaining_balance`, `payment_type`, `jatuh_tempo`, `status`, `created_at`, `updated_at`) VALUES
(1, 82, 'tunai', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 02:59:53', '2026-02-05 06:14:48'),
(2, 78, 'tunai', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 05:31:42', '2026-02-05 06:14:48'),
(3, 78, 'tunai', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 05:31:48', '2026-02-05 06:14:48'),
(4, 78, 'tunai', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 05:46:36', '2026-02-05 06:14:48'),
(5, 78, 'tunai', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 05:50:15', '2026-02-05 06:14:48'),
(6, 78, 'tunai', 0.00, 0.00, NULL, 0.00, 0.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 05:53:22', '2026-02-05 06:14:48'),
(7, 83, 'tunai', 100000.00, 0.00, NULL, 100000.00, 100000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 05:54:40', '2026-02-05 06:14:48'),
(8, 82, 'transfer', 120000.00, 0.00, NULL, 120000.00, 120000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 07:17:32', '2026-02-05 06:14:48'),
(9, 82, 'qris', 120000.00, 0.00, NULL, 120000.00, 120000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 07:25:53', '2026-02-05 06:14:48'),
(10, 83, 'transfer', 100000.00, 0.00, NULL, 100000.00, 100000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 08:19:45', '2026-02-05 06:14:48'),
(11, 82, 'tunai', 120000.00, 0.00, NULL, 120000.00, 120000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 08:54:35', '2026-02-05 06:14:48'),
(12, 85, 'tunai', 850000.00, 0.00, NULL, 850000.00, 850000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 09:11:16', '2026-02-05 06:14:48'),
(13, 83, 'transfer', 100000.00, 0.00, NULL, 100000.00, 100000.00, 0.00, 'full', '2026-01-26', 'paid', '2026-01-26 10:10:04', '2026-02-05 06:14:48'),
(16, 82, 'tunai', 120000.00, 1720400.00, NULL, 99600.00, 99600.00, 0.00, 'full', '2026-01-27', 'paid', '2026-01-26 18:12:06', '2026-02-05 06:14:48'),
(18, 84, 'tunai', 80000.00, 18400.00, 'persen', 61600.00, 61600.00, 0.00, 'full', '2026-01-29', 'paid', '2026-01-28 19:17:28', '2026-02-05 06:14:48'),
(19, 99, NULL, 275000.00, 0.00, 'nilai', 275000.00, 75000.00, 200000.00, 'partial', NULL, 'partial', '2026-02-05 08:21:01', '2026-02-05 08:21:01'),
(20, 99, 'tunai', 275000.00, 0.00, 'nilai', 275000.00, 75000.00, 125000.00, 'partial', '2026-02-04', 'partial', '2026-02-05 08:22:37', '2026-02-05 08:22:37'),
(21, 99, 'tunai', 275000.00, 0.00, 'nilai', 275000.00, 75000.00, 50000.00, 'partial', '2026-02-04', 'partial', '2026-02-05 08:27:13', '2026-02-05 08:27:13'),
(22, 99, 'tunai', 275000.00, 0.00, 'nilai', 275000.00, 50000.00, 0.00, 'full', '2026-02-04', 'paid', '2026-02-05 09:11:53', '2026-02-05 09:11:53'),
(23, 102, 'transfer', 1030000.00, 0.00, 'nilai', 1030000.00, 1030000.00, 0.00, 'full', '2026-02-04', 'paid', '2026-02-05 11:53:16', '2026-02-05 11:53:16'),
(24, 86, 'qris', 120000.00, 0.00, 'nilai', 120000.00, 108000.00, 12000.00, 'partial', '2026-02-02', 'partial', '2026-02-05 12:54:31', '2026-02-05 12:54:31'),
(25, 86, 'tunai', 120000.00, 0.00, 'nilai', 120000.00, 12000.00, 0.00, 'full', '2026-02-02', 'paid', '2026-02-05 12:54:53', '2026-02-05 12:54:53'),
(26, 91, 'debit', 75000.00, 0.00, 'nilai', 75000.00, 74950.00, 0.00, 'full', '2026-02-03', 'paid', '2026-02-05 12:57:09', '2026-02-05 12:57:09'),
(27, 91, 'qris', 75000.00, 0.00, 'nilai', 75000.00, 50.00, 0.00, 'full', '2026-02-03', 'paid', '2026-02-05 12:57:33', '2026-02-05 12:57:33'),
(28, 108, 'transfer', 350000.00, 0.00, 'none', 300000.00, 300000.00, 0.00, 'full', '2026-02-04', 'paid', '2026-02-05 13:17:11', '2026-02-05 13:17:11'),
(29, 108, 'transfer', 350000.00, 0.00, 'none', 345000.00, 40000.00, 5000.00, 'partial', '2026-02-04', 'partial', '2026-02-05 13:28:50', '2026-02-05 13:28:50'),
(30, 110, 'qris', 550000.00, 0.00, 'none', 530000.00, 530000.00, 0.00, 'full', '2026-02-06', 'paid', '2026-02-05 13:36:25', '2026-02-05 13:36:25'),
(31, 112, 'tunai', 100000.00, 0.00, 'none', 40000.00, 40000.00, 0.00, 'full', '2026-02-06', 'paid', '2026-02-05 13:51:58', '2026-02-05 13:51:58'),
(32, 105, 'transfer', 720000.00, 0.00, 'none', 565000.00, 565000.00, 0.00, 'full', '2026-02-04', 'paid', '2026-02-05 16:37:53', '2026-02-05 16:37:53'),
(33, 113, 'debit', 750000.00, 0.00, 'none', 225000.00, 225000.00, 0.00, 'full', '2026-02-06', 'paid', '2026-02-05 16:50:45', '2026-02-05 16:50:45'),
(34, 114, 'qris', 80000.00, 0.00, 'none', 24000.00, 24000.00, 0.00, 'full', '2026-02-07', 'paid', '2026-02-05 17:01:56', '2026-02-05 17:01:56'),
(35, 115, 'transfer', 200000.00, 42000.00, 'none', 158000.00, 158000.00, 0.00, 'full', '2026-02-07', 'paid', '2026-02-05 17:46:27', '2026-02-05 17:46:27'),
(36, 87, 'transfer', 350000.00, 35000.00, 'none', 315000.00, 315000.00, 0.00, 'full', '2026-02-18', 'paid', '2026-02-18 10:23:03', '2026-02-18 10:23:03'),
(37, 89, 'transfer', 800000.00, 50000.00, 'none', 750000.00, 750000.00, 0.00, 'full', '2026-02-18', 'paid', '2026-02-18 12:11:35', '2026-02-18 12:11:35'),
(38, 121, 'transfer', 900000.00, 20000.00, 'none', 880000.00, 880000.00, 0.00, 'full', '2026-02-19', 'paid', '2026-02-18 13:01:05', '2026-02-18 13:01:05'),
(39, 118, 'transfer', 450000.00, 45000.00, 'none', 405000.00, 405000.00, 0.00, 'full', '2026-02-13', 'paid', '2026-02-19 06:33:08', '2026-02-19 06:33:08'),
(40, 88, 'qris', 500000.00, 50000.00, 'item_diskon', 450000.00, 450000.00, 0.00, 'full', '2026-02-11', 'paid', '2026-02-19 06:44:42', '2026-02-19 06:44:42');

-- --------------------------------------------------------

--
-- Table structure for table `payments_backup_2026`
--

CREATE TABLE `payments_backup_2026` (
  `id` int NOT NULL DEFAULT '0',
  `booking_id` int NOT NULL,
  `metode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subtotal` int DEFAULT NULL,
  `diskon` int DEFAULT '0',
  `diskon_tipe` enum('persen','nilai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` int DEFAULT NULL,
  `status` enum('unpaid','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments_backup_2026`
--

INSERT INTO `payments_backup_2026` (`id`, `booking_id`, `metode`, `subtotal`, `diskon`, `diskon_tipe`, `total`, `status`, `created_at`) VALUES
(1, 82, 'tunai', 0, 0, NULL, 0, 'paid', '2026-01-26 02:59:53'),
(2, 78, 'tunai', 0, 0, NULL, 0, 'paid', '2026-01-26 05:31:42'),
(3, 78, 'tunai', 0, 0, NULL, 0, 'paid', '2026-01-26 05:31:48'),
(4, 78, 'tunai', 0, 0, NULL, 0, 'paid', '2026-01-26 05:46:36'),
(5, 78, 'tunai', 0, 0, NULL, 0, 'paid', '2026-01-26 05:50:15'),
(6, 78, 'tunai', 0, 0, NULL, 0, 'paid', '2026-01-26 05:53:22'),
(7, 83, 'tunai', 100000, 0, NULL, 100000, 'paid', '2026-01-26 05:54:40'),
(8, 82, 'transfer', 120000, 0, NULL, 120000, 'paid', '2026-01-26 07:17:32'),
(9, 82, 'qris', 120000, 0, NULL, 120000, 'paid', '2026-01-26 07:25:53'),
(10, 83, 'transfer', 100000, 0, NULL, 100000, 'paid', '2026-01-26 08:19:45'),
(11, 82, 'tunai', 120000, 0, NULL, 120000, 'paid', '2026-01-26 08:54:35'),
(12, 85, 'tunai', 850000, 0, NULL, 850000, 'paid', '2026-01-26 09:11:16'),
(13, 83, 'transfer', 100000, 0, NULL, 100000, 'paid', '2026-01-26 10:10:04'),
(16, 82, 'tunai', 120000, 1720400, NULL, 99600, 'paid', '2026-01-26 18:12:06'),
(18, 84, 'tunai', 80000, 18400, 'persen', 61600, 'paid', '2026-01-28 19:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `payment_installments`
--

CREATE TABLE `payment_installments` (
  `id` int NOT NULL,
  `payment_id` int DEFAULT NULL,
  `installment_number` int DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods_detail`
--

CREATE TABLE `payment_methods_detail` (
  `id` int NOT NULL,
  `payment_id` int NOT NULL,
  `metode` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_methods_detail`
--

INSERT INTO `payment_methods_detail` (`id`, `payment_id`, `metode`, `amount`, `reference`, `created_at`) VALUES
(1, 32, 'transfer', 565000.00, '', '2026-02-05 16:37:53'),
(2, 33, 'debit', 225000.00, '', '2026-02-05 16:50:45'),
(3, 34, 'qris', 24000.00, '', '2026-02-05 17:01:56'),
(4, 35, 'transfer', 158000.00, '', '2026-02-05 17:46:28'),
(5, 36, 'transfer', 315000.00, '', '2026-02-18 10:23:03'),
(6, 37, 'transfer', 750000.00, '', '2026-02-18 12:11:35'),
(7, 38, 'transfer', 880000.00, '', '2026-02-18 13:01:05'),
(8, 39, 'transfer', 405000.00, '', '2026-02-19 06:33:08'),
(9, 40, 'qris', 450000.00, '', '2026-02-19 06:44:42');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `kode_produk` varchar(50) DEFAULT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `deskripsi` text,
  `satuan` varchar(20) DEFAULT 'dosis',
  `harga` int DEFAULT '0',
  `minimal_stok` int DEFAULT '10',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `kode_produk`, `nama_produk`, `kategori`, `jenis`, `deskripsi`, `satuan`, `harga`, `minimal_stok`, `created_at`, `updated_at`) VALUES
(1, 'FLU-001', 'Vaksin Influenza', 'Influenza', 'Vaksin', '0', 'dosis', 235000, 10, '2026-02-12 13:22:19', '2026-02-18 10:41:28'),
(2, 'HPV-001', 'Vaksin HPV', 'HPV', 'Vaksin', 'Vaksin HPV untuk pencegahan kanker serviks. Untuk usia 9-26 tahun.', 'dosis', 364000, 10, '2026-02-12 13:22:19', '2026-02-13 07:29:28'),
(3, 'PCT-001', 'Paracetamol Infus', 'Antipiretik', 'Obat', 'Paracetamol infus untuk penurun panas dan pereda nyeri. Penggunaan sesuai resep dokter.', 'botol', 40000, 10, '2026-02-12 13:22:19', '2026-02-13 07:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `product_stock`
--

CREATE TABLE `product_stock` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `batch_number` varchar(100) NOT NULL,
  `expired_date` date NOT NULL,
  `stock` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`id`, `product_id`, `batch_number`, `expired_date`, `stock`, `created_at`, `updated_at`) VALUES
(1, 1, 'FLU-2025-001', '2026-10-15', 50, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(2, 1, 'FLU-2025-002', '2026-12-20', 25, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(3, 2, 'HPV-2025-001', '2027-01-30', 30, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(4, 3, 'PCT-2025-001', '2026-06-15', 100, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(5, 1, 'FLU-2025-009', '2026-02-28', 20, '2026-02-18 06:49:55', '2026-02-18 06:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `reservation_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `patient_id` int NOT NULL,
  `vaccine_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled','Rescheduled') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `total_price` decimal(10,2) DEFAULT '0.00',
  `payment_status` enum('Unpaid','Paid') COLLATE utf8mb4_general_ci DEFAULT 'Unpaid',
  `notes` text COLLATE utf8mb4_general_ci,
  `reminder_h_minus_1` tinyint(1) DEFAULT '0',
  `reminder_h_plus_1` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `kode_layanan` varchar(50) DEFAULT NULL,
  `nama_layanan` varchar(255) NOT NULL,
  `kategori_usia` enum('Anak','Dewasa','Semua Usia') DEFAULT 'Semua Usia',
  `tipe` enum('pelayanan','paket','jasa') NOT NULL,
  `deskripsi` text,
  `harga` int DEFAULT '0',
  `kode_paket` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `kode_layanan`, `nama_layanan`, `kategori_usia`, `tipe`, `deskripsi`, `harga`, `kode_paket`, `created_at`, `updated_at`) VALUES
(1, 'SVC-FLU', 'Vaksinasi Influenza', 'Semua Usia', 'pelayanan', NULL, 250000, NULL, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(2, 'SVC-HPV', 'Vaksinasi HPV', 'Anak', 'pelayanan', NULL, 350000, NULL, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(3, 'SVC-INF', 'Infus Obat Demam', 'Semua Usia', 'pelayanan', NULL, 200000, NULL, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(4, 'PKG-HPV-3', 'Paket Vaksinasi HPV 3 Dosis', 'Anak', 'paket', NULL, 950000, 'HPV-3X', '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(5, 'PKG-FLU-2', 'Paket Vaksinasi Influenza 2 Dosis', 'Semua Usia', 'paket', NULL, 450000, 'FLU-2X', '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(6, NULL, 'Vaksinasi Influenza', 'Semua Usia', 'pelayanan', NULL, 250000, NULL, '2026-02-12 14:24:08', '2026-02-12 14:24:08'),
(7, NULL, 'Vaksinasi HPV', 'Anak', 'pelayanan', NULL, 350000, NULL, '2026-02-12 14:24:08', '2026-02-12 14:24:08'),
(9, NULL, 'Infus Vitamin C', 'Semua Usia', 'pelayanan', NULL, 200000, NULL, '2026-02-12 14:24:08', '2026-02-12 14:24:08'),
(10, NULL, 'Infus Obat Demam', 'Semua Usia', 'pelayanan', NULL, 180000, NULL, '2026-02-12 14:24:08', '2026-02-12 14:24:08'),
(11, NULL, 'Medical Check Up', 'Dewasa', 'pelayanan', NULL, 500000, NULL, '2026-02-12 14:24:08', '2026-02-12 14:24:08'),
(12, 'JAS-001', 'Jasa Dokter Umum', 'Semua Usia', 'jasa', 'Konsultasi dan pemeriksaan oleh dokter umum', 75000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(13, 'JAS-002', 'Jasa Dokter Spesialis Anak', 'Anak', 'jasa', 'Konsultasi dokter spesialis anak', 150000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(14, 'JAS-004', 'Jasa Perawat Vaksinator', 'Semua Usia', 'jasa', 'Tindakan penyuntikan vaksin oleh perawat terlatih', 50000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(15, 'JAS-005', 'Jasa Perawat Klinik', 'Semua Usia', 'jasa', 'Tindakan keperawatan umum', 40000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(16, 'JAS-006', 'Jasa Perawat Home Care', 'Semua Usia', 'jasa', 'Kunjungan perawat ke rumah', 75000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(17, 'JAS-014', 'Skrining Kesehatan Awal', 'Semua Usia', 'jasa', 'Pemeriksaan kesehatan awal', 35000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(18, 'JAS-015', 'Cek Tekanan Darah', 'Semua Usia', 'jasa', 'Pemeriksaan tekanan darah', 20000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44'),
(19, 'JAS-016', 'Cek Suhu Tubuh', 'Semua Usia', 'jasa', 'Pemeriksaan suhu tubuh', 10000, NULL, '2026-02-14 18:01:14', '2026-02-14 18:03:44');

--
-- Triggers `services`
--
DELIMITER $$
CREATE TRIGGER `services_before_update` BEFORE UPDATE ON `services` FOR EACH ROW BEGIN
    SET NEW.updated_at = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `service_components`
--

CREATE TABLE `service_components` (
  `id` int NOT NULL,
  `service_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_components`
--

INSERT INTO `service_components` (`id`, `service_id`, `product_id`, `quantity`, `created_at`) VALUES
(1, 1, 1, 1, '2026-02-12 13:22:19'),
(2, 2, 2, 1, '2026-02-12 13:22:19'),
(3, 3, 3, 1, '2026-02-12 13:22:19'),
(6, 6, 1, 1, '2026-02-14 17:43:21'),
(7, 7, 2, 1, '2026-02-14 17:47:35');

-- --------------------------------------------------------

--
-- Table structure for table `service_jasa_components`
--

CREATE TABLE `service_jasa_components` (
  `id` int NOT NULL,
  `service_id` int NOT NULL,
  `jasa_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_jasa_components`
--

INSERT INTO `service_jasa_components` (`id`, `service_id`, `jasa_id`, `quantity`, `created_at`, `updated_at`) VALUES
(3, 6, 12, 1, '2026-02-14 18:55:10', NULL),
(4, 7, 15, 1, '2026-02-14 19:17:46', NULL),
(5, 6, 17, 1, '2026-02-18 07:01:01', NULL);

--
-- Triggers `service_jasa_components`
--
DELIMITER $$
CREATE TRIGGER `service_jasa_components_before_update` BEFORE UPDATE ON `service_jasa_components` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `service_package_items`
--

CREATE TABLE `service_package_items` (
  `id` int NOT NULL,
  `package_id` int NOT NULL,
  `service_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `visit_order` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_package_items`
--

INSERT INTO `service_package_items` (`id`, `package_id`, `service_id`, `quantity`, `visit_order`, `created_at`) VALUES
(1, 4, 2, 1, 1, '2026-02-12 13:22:19'),
(2, 4, 2, 1, 2, '2026-02-12 13:22:19'),
(3, 4, 2, 1, 3, '2026-02-12 13:22:19'),
(4, 5, 1, 1, 1, '2026-02-12 13:22:19'),
(5, 5, 1, 1, 2, '2026-02-12 13:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `service_product_components`
--

CREATE TABLE `service_product_components` (
  `id` int NOT NULL,
  `service_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_product_components`
--

INSERT INTO `service_product_components` (`id`, `service_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(2, 6, 1, 1, '2026-02-14 17:43:21', '2026-02-14 17:43:21'),
(3, 2, 2, 1, '2026-02-12 13:22:19', '2026-02-12 13:22:19'),
(4, 7, 2, 1, '2026-02-14 17:47:35', '2026-02-14 17:47:35'),
(5, 3, 3, 1, '2026-02-12 13:22:19', '2026-02-12 13:22:19');

--
-- Triggers `service_product_components`
--
DELIMITER $$
CREATE TRIGGER `service_product_components_before_update` BEFORE UPDATE ON `service_product_components` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int NOT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `gelar` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('dokter','perawat','admin') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `nama_lengkap`, `gelar`, `sip`, `role`, `created_at`) VALUES
(1, 'Anna Rahma', 'dr.', '123/SIP/2024', 'dokter', '2026-01-20 03:23:31'),
(2, 'Budi Santoso', 'dr.', NULL, 'dokter', '2026-01-20 03:23:31'),
(3, 'Dewi Lestari', 'dr.', NULL, 'dokter', '2026-01-20 03:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` int NOT NULL,
  `booking_id` int DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `jenis_surat` enum('sehat','sakit','vaksin') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dokter_id` int DEFAULT NULL,
  `posisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `lama_istirahat` int DEFAULT NULL,
  `tgl_awal` date DEFAULT NULL,
  `tgl_akhir` date DEFAULT NULL,
  `pf_lain` text COLLATE utf8mb4_general_ci,
  `jenis_vaksin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `batch_vaksin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expired_vaksin` date DEFAULT NULL,
  `file_pdf` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `booking_id`, `patient_id`, `jenis_surat`, `dokter_id`, `posisi`, `tanggal_surat`, `lama_istirahat`, `tgl_awal`, `tgl_akhir`, `pf_lain`, `jenis_vaksin`, `batch_vaksin`, `expired_vaksin`, `file_pdf`, `created_at`) VALUES
(1, 78, 1, 'sakit', 1, 'Dokter Penanggung Jawab', '2026-01-23', 0, '0000-00-00', '0000-00-00', '', 'a', '123', '2026-01-31', NULL, '2026-01-23 08:46:51'),
(2, 83, 1, 'vaksin', 1, 'Dokter Penanggung Jawab', '2026-01-27', NULL, NULL, NULL, NULL, 'a', 'a', '2026-01-24', 'surat_1769533341_83.pdf', '2026-01-27 17:02:21'),
(3, 83, 1, 'sehat', 1, 'Dokter Penanggung Jawab', '2026-01-27', NULL, NULL, NULL, NULL, 'a', 'a', '2026-01-24', 'surat_1769533378_83.pdf', '2026-01-27 17:02:58'),
(4, 82, 5, 'sehat', 2, 'Dokter Penanggung Jawab', '2026-01-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'surat_1769534100_82.pdf', '2026-01-27 17:15:00'),
(5, 82, 5, 'sakit', 2, 'Dokter Penanggung Jawab', '2026-01-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'surat_1769534233_82.pdf', '2026-01-27 17:17:13'),
(6, 99, 2, 'vaksin', 1, 'Dokter Penanggung Jawab', '2026-02-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'surat_1770211743_99.pdf', '2026-02-04 13:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `max_capacity` int DEFAULT '3',
  `current_booking` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1'
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
-- Table structure for table `tindakan`
--

CREATE TABLE `tindakan` (
  `id` int NOT NULL,
  `booking_id` int DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `keluhan` text COLLATE utf8mb4_general_ci,
  `kipi_sebelumnya` text COLLATE utf8mb4_general_ci,
  `kontraindikasi` text COLLATE utf8mb4_general_ci,
  `anamnesis` text COLLATE utf8mb4_general_ci,
  `pemeriksaan_fisik` text COLLATE utf8mb4_general_ci,
  `diagnosis` text COLLATE utf8mb4_general_ci,
  `tatalaksana` text COLLATE utf8mb4_general_ci,
  `suhu` decimal(4,1) DEFAULT NULL,
  `tekanan_darah` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `respirasi` int DEFAULT NULL,
  `nadi` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `jenis_vaksin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `batch_vaksin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expired_vaksin` date DEFAULT NULL,
  `kedatangan_ke` int DEFAULT NULL,
  `kedatangan_selanjutnya` date DEFAULT NULL,
  `bb` float DEFAULT NULL,
  `tb` float DEFAULT NULL,
  `lingkar_kepala` float DEFAULT NULL,
  `pf_lainnya` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tindakan`
--

INSERT INTO `tindakan` (`id`, `booking_id`, `patient_id`, `keluhan`, `kipi_sebelumnya`, `kontraindikasi`, `anamnesis`, `pemeriksaan_fisik`, `diagnosis`, `tatalaksana`, `suhu`, `tekanan_darah`, `respirasi`, `nadi`, `status`, `created_at`, `updated_at`, `jenis_vaksin`, `batch_vaksin`, `expired_vaksin`, `kedatangan_ke`, `kedatangan_selanjutnya`, `bb`, `tb`, `lingkar_kepala`, `pf_lainnya`) VALUES
(2, 78, 1, NULL, NULL, NULL, 'a', 's', 'p', 'f', 36.0, '120', 0, 0, '0', '2026-01-23 08:53:46', NULL, 'a', 'a', '2026-01-24', 1, NULL, NULL, NULL, NULL, NULL),
(3, 80, 3, NULL, NULL, NULL, 'p', 'p', 'p', 'p', 30.0, '', 0, 0, '0', '2026-01-23 08:53:39', NULL, '', '', NULL, 1, NULL, NULL, NULL, NULL, NULL),
(4, 83, 1, NULL, NULL, NULL, 'a', 'a', 'a', 'a', 36.0, '120', 0, 0, '0', '2026-01-26 04:00:59', NULL, 'a', 'a', '2026-01-24', 1, NULL, NULL, NULL, NULL, NULL),
(5, 84, 6, NULL, NULL, NULL, 'a', 'b', 'c', 'd', 36.0, '', 0, 0, '0', '2026-01-26 11:46:42', NULL, '', '', NULL, 1, NULL, NULL, NULL, NULL, NULL),
(6, 82, 5, NULL, NULL, NULL, 'a', '', '', '', NULL, '', 0, 0, '0', '2026-01-27 11:26:50', NULL, '', '', NULL, 1, NULL, NULL, NULL, NULL, NULL),
(7, 99, 2, NULL, NULL, NULL, '', 'a', '', '', NULL, '', 0, 0, '0', '2026-02-04 07:11:42', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(8, 101, 14, NULL, NULL, NULL, '', 'b', '', '', NULL, '', 0, 0, '0', '2026-02-04 07:26:56', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(9, 102, 1, NULL, NULL, NULL, 'b', '', '', '', NULL, '', 0, 0, '0', '2026-02-05 03:51:47', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(10, 103, 15, NULL, NULL, NULL, 'b', '', '', '', NULL, '', 0, 0, '0', '2026-02-05 03:52:01', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(11, 104, 16, NULL, NULL, NULL, '', '', '', 'c', NULL, '', 0, 0, '0', '2026-02-05 03:52:08', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(12, 86, 1, NULL, NULL, NULL, 'a', '', '', '', NULL, '', 0, 0, '0', '2026-02-05 06:53:58', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(13, 91, 8, NULL, NULL, NULL, '', '', 'k', '', NULL, '', 0, 0, '0', '2026-02-05 06:56:06', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(14, 108, 19, NULL, NULL, NULL, '', 'g', '', '', NULL, '', 0, 0, '0', '2026-02-05 07:13:44', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(15, 109, 20, NULL, NULL, NULL, '', '', 'q', '', NULL, '', 0, 0, '0', '2026-02-05 07:13:49', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(16, 110, 21, NULL, NULL, NULL, '', 't', '', '', NULL, '', 0, 0, '0', '2026-02-05 07:34:49', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(17, 111, 22, NULL, NULL, NULL, '', '', '', 'e', NULL, '', 0, 0, '0', '2026-02-05 07:34:53', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(18, 112, 23, NULL, NULL, NULL, '', 'u', '', '', NULL, '', 0, 0, '0', '2026-02-05 07:51:31', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(19, 105, 17, NULL, NULL, NULL, 'a', '', '', '', NULL, '', 0, 0, '0', '2026-02-05 10:36:58', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(20, 106, 2, NULL, NULL, NULL, '', 'a', '', '', NULL, '', 0, 0, '0', '2026-02-05 10:37:03', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(21, 107, 18, NULL, NULL, NULL, '', '', 'a', '', NULL, '', 0, 0, '0', '2026-02-05 10:37:08', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(22, 81, 4, NULL, NULL, NULL, '', 'p', '', '', NULL, '', 0, 0, '0', '2026-02-05 10:47:40', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(23, 113, 24, NULL, NULL, NULL, 'p', '', '', '', NULL, '', 0, 0, '0', '2026-02-05 10:50:19', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(24, 114, 25, NULL, NULL, NULL, '', '', '', 'a', NULL, '', 0, 0, '0', '2026-02-05 11:00:51', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(25, 115, 26, NULL, NULL, NULL, '', '', '', '', NULL, '', 0, 0, '0', '2026-02-05 11:16:10', NULL, '', '', NULL, 1, NULL, NULL, NULL, NULL, NULL),
(26, 87, 1, NULL, NULL, NULL, '', '', '', '', NULL, '', 0, 0, '0', '2026-02-18 04:22:04', NULL, 'a', 'a', '2026-02-26', 1, NULL, NULL, NULL, NULL, NULL),
(27, 89, 2, NULL, NULL, NULL, '', '', 'Pro Vaksinasi (Z23)', '', NULL, '', 0, 0, '0', '2026-02-18 06:10:58', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(28, 122, 31, NULL, NULL, NULL, '', '', 'Pro Vaksinasi (Z23)', '', NULL, '', 0, 0, '0', '2026-02-18 07:00:23', NULL, '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(29, 121, 30, NULL, NULL, NULL, '', '', '', '', NULL, '', 0, 0, '0', '2026-02-18 07:00:33', NULL, '', '', NULL, 1, NULL, NULL, NULL, NULL, NULL),
(30, 88, 2, NULL, NULL, NULL, '', '', '', '', NULL, '', 0, 0, '0', '2026-02-19 00:31:59', NULL, 'a', '', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(31, 118, 1, NULL, NULL, NULL, '', '', '', '', NULL, '', 0, 0, '0', '2026-02-19 00:32:41', NULL, '', 'a', NULL, 0, NULL, NULL, NULL, NULL, NULL),
(32, 119, 28, 'Tidak ada keluhan', 'Tidak ada', 'Tidak ada', '', '', '', '', NULL, '', NULL, NULL, '', '2026-02-19 02:01:44', '2026-02-19 21:54:23', '', '', NULL, NULL, '2026-02-28', NULL, NULL, NULL, 'Dalam batas normal');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_history`
--

CREATE TABLE `vaccination_history` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `vaccine_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `vaccination_date` date NOT NULL,
  `location` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `batch_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vaccines`
--

CREATE TABLE `vaccines` (
  `id` int NOT NULL,
  `vaccine_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) DEFAULT '0.00',
  `stock` int DEFAULT '0',
  `min_age` int DEFAULT '0',
  `max_age` int DEFAULT '100',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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

-- --------------------------------------------------------

--
-- Table structure for table `vital_signs`
--

CREATE TABLE `vital_signs` (
  `id` int NOT NULL,
  `action_id` int NOT NULL,
  `suhu` decimal(4,1) DEFAULT NULL,
  `tekanan_darah` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `respirasi` int DEFAULT NULL,
  `nadi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_tanggal` (`tanggal_booking`),
  ADD KEY `idx_waktu` (`waktu_booking`),
  ADD KEY `idx_tanggal_waktu` (`tanggal_booking`,`waktu_booking`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_nomor_antrian` (`nomor_antrian`),
  ADD KEY `fk_doctor_id` (`doctor_id`);

--
-- Indexes for table `booking_services`
--
ALTER TABLE `booking_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking` (`booking_id`);

--
-- Indexes for table `booking_staff`
--
ALTER TABLE `booking_staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `jadwal_khusus`
--
ALTER TABLE `jadwal_khusus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_rentang` (`tanggal_mulai`,`tanggal_selesai`);

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
-- Indexes for table `medical_actions`
--
ALTER TABLE `medical_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `medical_letters`
--
ALTER TABLE `medical_letters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `action_id` (`action_id`),
  ADD KEY `dokter_id` (`dokter_id`);

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
  ADD UNIQUE KEY `no_rekam_medis_2` (`no_rekam_medis`),
  ADD UNIQUE KEY `unique_nik` (`nik`),
  ADD UNIQUE KEY `unique_paspor` (`paspor`);

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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_payments_booking_status` (`booking_id`,`status`),
  ADD KEY `idx_payments_jatuh_tempo` (`jatuh_tempo`);

--
-- Indexes for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `payment_methods_detail`
--
ALTER TABLE `payment_methods_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_methods_payment` (`payment_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`);

--
-- Indexes for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_expired` (`expired_date`);

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
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_layanan` (`kode_layanan`),
  ADD KEY `idx_tipe` (`tipe`),
  ADD KEY `idx_kategori` (`kategori_usia`);

--
-- Indexes for table `service_components`
--
ALTER TABLE `service_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_service_id` (`service_id`);

--
-- Indexes for table `service_jasa_components`
--
ALTER TABLE `service_jasa_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `jasa_id` (`jasa_id`);

--
-- Indexes for table `service_package_items`
--
ALTER TABLE `service_package_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_package_id` (`package_id`);

--
-- Indexes for table `service_product_components`
--
ALTER TABLE `service_product_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`slot_date`,`slot_time`);

--
-- Indexes for table `tindakan`
--
ALTER TABLE `tindakan`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `action_id` (`action_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `booking_services`
--
ALTER TABLE `booking_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `booking_staff`
--
ALTER TABLE `booking_staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `jadwal_khusus`
--
ALTER TABLE `jadwal_khusus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jadwal_klinik`
--
ALTER TABLE `jadwal_klinik`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `jadwal_libur`
--
ALTER TABLE `jadwal_libur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `kipi_records`
--
ALTER TABLE `kipi_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_actions`
--
ALTER TABLE `medical_actions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_letters`
--
ALTER TABLE `medical_letters`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `patient_addresses`
--
ALTER TABLE `patient_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `patient_emails`
--
ALTER TABLE `patient_emails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `patient_phones`
--
ALTER TABLE `patient_phones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `patient_services`
--
ALTER TABLE `patient_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `payment_installments`
--
ALTER TABLE `payment_installments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods_detail`
--
ALTER TABLE `payment_methods_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `service_components`
--
ALTER TABLE `service_components`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `service_jasa_components`
--
ALTER TABLE `service_jasa_components`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_package_items`
--
ALTER TABLE `service_package_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_product_components`
--
ALTER TABLE `service_product_components`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tindakan`
--
ALTER TABLE `tindakan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `vaccination_history`
--
ALTER TABLE `vaccination_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vaccines`
--
ALTER TABLE `vaccines`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vital_signs`
--
ALTER TABLE `vital_signs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_doctor_id` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_services`
--
ALTER TABLE `booking_services`
  ADD CONSTRAINT `booking_services_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_staff`
--
ALTER TABLE `booking_staff`
  ADD CONSTRAINT `booking_staff_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_staff_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kipi_records`
--
ALTER TABLE `kipi_records`
  ADD CONSTRAINT `kipi_records_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kipi_records_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_actions`
--
ALTER TABLE `medical_actions`
  ADD CONSTRAINT `medical_actions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `medical_actions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `medical_letters`
--
ALTER TABLE `medical_letters`
  ADD CONSTRAINT `medical_letters_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `medical_actions` (`id`),
  ADD CONSTRAINT `medical_letters_ibfk_2` FOREIGN KEY (`dokter_id`) REFERENCES `staff` (`id`);

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
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD CONSTRAINT `payment_installments_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `payment_methods_detail`
--
ALTER TABLE `payment_methods_detail`
  ADD CONSTRAINT `payment_methods_detail_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD CONSTRAINT `product_stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `time_slots` (`id`);

--
-- Constraints for table `service_components`
--
ALTER TABLE `service_components`
  ADD CONSTRAINT `service_components_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_components_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `service_jasa_components`
--
ALTER TABLE `service_jasa_components`
  ADD CONSTRAINT `service_jasa_components_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `service_jasa_components_ibfk_2` FOREIGN KEY (`jasa_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `service_package_items`
--
ALTER TABLE `service_package_items`
  ADD CONSTRAINT `service_package_items_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_package_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `service_product_components`
--
ALTER TABLE `service_product_components`
  ADD CONSTRAINT `service_product_components_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `service_product_components_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `vaccination_history`
--
ALTER TABLE `vaccination_history`
  ADD CONSTRAINT `vaccination_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD CONSTRAINT `vital_signs_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `medical_actions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
