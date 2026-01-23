-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 03:23 PM
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `doctor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `patient_id`, `service_type`, `nomor_antrian`, `tanggal_booking`, `waktu_booking`, `status`, `catatan`, `created_at`, `updated_at`, `doctor_id`) VALUES
(78, 1, 'In Clinic', '20260120-001', '2026-01-23', '09:30:00', 'confirmed', 'Pendaftaran online', '2026-01-20 09:38:53', '2026-01-23 21:18:33', 1),
(79, 2, 'In Clinic', '20260121-001', '2026-01-23', '09:00:00', 'confirmed', 'Pendaftaran online', '2026-01-20 23:23:09', '2026-01-23 21:21:20', NULL),
(80, 3, 'In Clinic', '20260122-002', '2026-01-23', '11:00:00', 'pending', 'Pendaftaran online', '2026-01-22 15:53:09', '2026-01-23 21:10:50', NULL),
(81, 4, 'In Clinic', '20260122-003', '2026-01-23', '10:30:00', 'pending', 'Pendaftaran online', '2026-01-22 17:17:05', '2026-01-23 21:11:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_services`
--

CREATE TABLE `booking_services` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_services`
--

INSERT INTO `booking_services` (`id`, `booking_id`, `nama_layanan`, `created_at`) VALUES
(18, 78, 'Adacel (Sanofi)', '2026-01-20 09:38:53'),
(19, 79, 'Campak (Biofarma)', '2026-01-20 23:23:09'),
(20, 79, 'Swab Antigen COVID-19', '2026-01-20 23:23:09'),
(21, 80, 'Vitamin Badan Bugar', '2026-01-22 15:53:09'),
(22, 80, 'Vitamin D3', '2026-01-22 15:53:09'),
(23, 81, 'Pantoprazole 40 mg Vial', '2026-01-22 17:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `booking_staff`
--

CREATE TABLE `booking_staff` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_staff`
--

INSERT INTO `booking_staff` (`id`, `booking_id`, `staff_id`, `created_at`) VALUES
(11, 79, 1, '2026-01-23 14:18:21'),
(12, 78, 2, '2026-01-23 14:18:33');

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
-- Table structure for table `medical_actions`
--

CREATE TABLE `medical_actions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `layanan` text DEFAULT NULL,
  `tanggal_vaksinasi` date DEFAULT NULL,
  `jenis_vaksin` varchar(100) DEFAULT NULL,
  `batch_vaksin` varchar(100) DEFAULT NULL,
  `expired_vaksin` date DEFAULT NULL,
  `kedatangan_ke` int(11) DEFAULT NULL,
  `kedatangan_selanjutnya` int(11) DEFAULT NULL,
  `status` enum('Aktif','Selesai') DEFAULT 'Aktif',
  `anamnesis` text DEFAULT NULL,
  `pemeriksaan_fisik` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `tatalaksana` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_letters`
--

CREATE TABLE `medical_letters` (
  `id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `jenis` enum('sehat','sakit','vaksin') DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `dokter_id` int(11) DEFAULT NULL,
  `posisi` varchar(100) DEFAULT NULL,
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
(1, 'RM202601200001', 'Rofi\'ah Budi Nadia', 'fiah', '2003-01-07', 23, 'Dewasa', 'P', '3314014701030001', '', 'Indonesia', 'umum', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-20 02:38:53', '2026-01-21 13:47:54'),
(2, 'RM202601200002', 'Leo', '', '2000-04-21', 25, 'Dewasa', 'L', '3314123456789098', NULL, 'Indonesia', 'karyawan swasta', '', 'sifud', 'sakit kepala', 'alkohol', 'Vaksinasi Umum/Infus Vitamin', '2026-01-20 16:23:09', '2026-01-22 08:15:19'),
(3, 'RM202601220001', 'Dillon', '', '1999-01-28', 26, 'Dewasa', 'L', '3314567654890765', NULL, 'Indonesia', 'karyawan swasta', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-22 08:53:09', '2026-01-22 08:53:09'),
(4, 'RM202601220002', 'Joya', '', '2002-06-19', 23, 'Dewasa', 'P', '3314565428790654', NULL, 'Indonesia', 'nganggur', '', '', '', '', 'Vaksinasi Umum/Infus Vitamin', '2026-01-22 10:17:05', '2026-01-22 10:17:05');

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
(74, 1, 'Salam, Rt.13, Saren, Kalijambe', 'Jawa Tengah', 'Sragen', 1),
(75, 2, 'Bandung - Jawa Barat', 'Jawa Barat', 'Cimahi', 1),
(76, 3, 'cribon', 'Jawa Barat', 'Cirebon', 1),
(77, 4, 'seattle', 'DKI Jakarta', 'Jakarta Utara', 1);

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
(76, 1, 'rofiahbudi@gmail.com', 1),
(77, 2, 'rangga@gmail.com', 1),
(78, 3, 'dillon@gmail.com', 1),
(79, 4, 'joya@gmail.com', 1);

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
(77, 1, '085876923088', 1),
(78, 2, '085787652345', 1),
(79, 3, '087654323456', 1),
(80, 4, '087698764536', 1);

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
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `gelar` varchar(50) DEFAULT NULL,
  `role` enum('dokter','perawat','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `nama_lengkap`, `gelar`, `role`, `created_at`) VALUES
(1, 'Anna Rahma', 'dr.', 'dokter', '2026-01-20 03:23:31'),
(2, 'Budi Santoso', 'dr.', 'dokter', '2026-01-20 03:23:31'),
(3, 'Dewi Lestari', 'dr.', 'dokter', '2026-01-20 03:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `jenis_surat` enum('sehat','sakit','vaksin') DEFAULT NULL,
  `dokter_id` int(11) DEFAULT NULL,
  `posisi` varchar(100) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `lama_istirahat` int(11) DEFAULT NULL,
  `tgl_awal` date DEFAULT NULL,
  `tgl_akhir` date DEFAULT NULL,
  `pf_lain` text DEFAULT NULL,
  `jenis_vaksin` varchar(100) DEFAULT NULL,
  `batch_vaksin` varchar(100) DEFAULT NULL,
  `expired_vaksin` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `booking_id`, `patient_id`, `jenis_surat`, `dokter_id`, `posisi`, `tanggal_surat`, `lama_istirahat`, `tgl_awal`, `tgl_akhir`, `pf_lain`, `jenis_vaksin`, `batch_vaksin`, `expired_vaksin`, `created_at`) VALUES
(1, 78, 1, 'sakit', 1, 'Dokter Penanggung Jawab', '2026-01-23', 0, '0000-00-00', '0000-00-00', '', 'a', '123', '2026-01-31', '2026-01-23 08:46:51');

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
-- Table structure for table `tindakan`
--

CREATE TABLE `tindakan` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `anamnesis` text DEFAULT NULL,
  `pemeriksaan_fisik` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `tatalaksana` text DEFAULT NULL,
  `suhu` decimal(4,1) DEFAULT NULL,
  `tekanan_darah` varchar(10) DEFAULT NULL,
  `respirasi` int(11) DEFAULT NULL,
  `nadi` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis_vaksin` varchar(100) DEFAULT NULL,
  `batch_vaksin` varchar(100) DEFAULT NULL,
  `expired_vaksin` date DEFAULT NULL,
  `kedatangan_ke` int(11) DEFAULT NULL,
  `kedatangan_selanjutnya` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tindakan`
--

INSERT INTO `tindakan` (`id`, `booking_id`, `patient_id`, `anamnesis`, `pemeriksaan_fisik`, `diagnosis`, `tatalaksana`, `suhu`, `tekanan_darah`, `respirasi`, `nadi`, `status`, `created_at`, `jenis_vaksin`, `batch_vaksin`, `expired_vaksin`, `kedatangan_ke`, `kedatangan_selanjutnya`) VALUES
(2, 78, 1, 'a', 's', 'd', 'f', 36.0, '120', 0, 0, '0', '2026-01-23 08:53:46', 'a', 'a', '2026-01-24', 1, 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `vital_signs`
--

CREATE TABLE `vital_signs` (
  `id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `suhu` decimal(4,1) DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `respirasi` int(11) DEFAULT NULL,
  `nadi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_code` (`reservation_code`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `vaccine_id` (`vaccine_id`),
  ADD KEY `slot_id` (`slot_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `booking_services`
--
ALTER TABLE `booking_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `booking_staff`
--
ALTER TABLE `booking_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `jadwal_klinik`
--
ALTER TABLE `jadwal_klinik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jadwal_libur`
--
ALTER TABLE `jadwal_libur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `kipi_records`
--
ALTER TABLE `kipi_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_actions`
--
ALTER TABLE `medical_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_letters`
--
ALTER TABLE `medical_letters`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patient_addresses`
--
ALTER TABLE `patient_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `patient_emails`
--
ALTER TABLE `patient_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `patient_phones`
--
ALTER TABLE `patient_phones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

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
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tindakan`
--
ALTER TABLE `tindakan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `vital_signs`
--
ALTER TABLE `vital_signs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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

--
-- Constraints for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD CONSTRAINT `vital_signs_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `medical_actions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
