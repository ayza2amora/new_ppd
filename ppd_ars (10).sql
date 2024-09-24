-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 12, 2024 at 03:09 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ppd_ars`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `previous_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_logs_user_id_foreign` (`user_id`),
  KEY `fk_changed_by` (`changed_by`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `user_id`, `action`, `previous_value`, `new_value`, `changed_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'program_change', 'Supplementary Feeding Program (SFP)', 'Social Pension (SocPen) for Indigent Senior Citizen Program', 9, '2024-09-12 01:45:02', '2024-09-12 01:45:02'),
(2, 2, 'role_change', 'user', 'admin', 9, '2024-09-12 01:45:05', '2024-09-12 01:45:05'),
(3, 2, 'activate', 'inactive', 'active', 9, '2024-09-12 01:45:08', '2024-09-12 01:45:08'),
(4, 2, 'deactivate', 'active', 'inactive', 9, '2024-09-12 01:45:11', '2024-09-12 01:45:11'),
(8, 9, 'create_program', NULL, 'p', 9, '2024-09-12 02:02:02', '2024-09-12 02:02:02'),
(6, 9, 'create_program', NULL, 'a', 9, '2024-09-12 01:46:14', '2024-09-12 01:46:14'),
(7, 9, 'update_program', 'b', 'c', NULL, '2024-09-12 01:46:22', '2024-09-12 01:46:22'),
(9, 9, 'update_program', 'c', 'd', NULL, '2024-09-12 02:02:12', '2024-09-12 02:02:12'),
(10, 1, 'role_change', 'admin', 'user', 9, '2024-09-12 02:26:33', '2024-09-12 02:26:33'),
(11, 1, 'program_change', 'Disaster Response and Management Program (DRMP)', 'Sustainable Livelihood Program (SLP)', 9, '2024-09-12 02:27:20', '2024-09-12 02:27:20');

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

DROP TABLE IF EXISTS `allocations`;
CREATE TABLE IF NOT EXISTS `allocations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `province` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_municipality` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `program` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` int NOT NULL,
  `fund_allocation` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `allocations`
--

INSERT INTO `allocations` (`id`, `province`, `city_municipality`, `program`, `target`, `fund_allocation`, `created_at`, `updated_at`) VALUES
(1, 'DAVAO DEL NORTE', 'CARMEN', 'Centenarian', 123, 43234, '2024-08-28 19:48:59', '2024-08-28 19:51:39'),
(2, 'DAVAO DEL SUR', 'CITY OF DIGOS', 'Sustainable Livelihood Program (SLP)', 42, 43123, '2024-08-28 19:49:15', '2024-09-06 20:04:41'),
(3, 'DAVAO ORIENTAL', 'BAGANGA - 1st CONGRESSIONAL DISTRICT', 'Centenarian', 33, 42424, '2024-08-28 19:50:32', '2024-09-06 20:04:55'),
(4, 'DAVAO DE ORO', 'COMPOSTELA- 1st CONGRESSIONAL DISTRICT', 'Assistance to Individuals in Crisis Situation (AICS)', 12, 45221, '2024-09-01 15:37:54', '2024-09-08 18:16:32'),
(5, 'DAVAO OCCIDENTAL', 'STA.MARIA', 'Assistance to Individuals in Crisis Situation (AICS)', 11, 44353, '2024-09-01 15:38:23', '2024-09-11 13:18:51'),
(10, 'DAVAO CITY', '1ST CONGRESSIONAL DISTRICT', 'Social Pension (SocPen) for Indigent Senior Citizen Program', 12, 43212, '2024-09-05 18:14:25', '2024-09-05 18:14:25'),
(7, 'DAVAO DEL SUR', 'HAGONOY', 'Centenarian', 213, 214, '2024-09-02 16:02:13', '2024-09-02 16:03:02'),
(8, 'DAVAO DEL SUR', 'BANSALAN', 'Centenarian', 1, 1, '2024-09-04 18:02:11', '2024-09-04 18:02:11'),
(9, 'DAVAO DEL SUR', 'MAGSAYSAY', 'Pantawid Pamilyang Pilipino Program (4P\'s)', 1, 1, '2024-09-04 20:18:06', '2024-09-04 20:18:06'),
(11, 'DAVAO DEL NORTE', 'CARMEN - 2nd CONGRESSIONAL DISTRICT', 'Supplementary Feeding Program (SFP)', 12, 12, '2024-09-06 20:01:53', '2024-09-06 20:01:53'),
(12, 'DAVAO DEL NORTE', 'ASUNCION - 1st CONGRESSIONAL DISTRICT', 'Assistance to Individuals in Crisis Situation (AICS)', 13, 231, '2024-09-08 18:14:52', '2024-09-08 18:14:52'),
(13, 'DAVAO DE ORO', 'MABINI - 2nd CONGRESSIONAL DISTRICT', 'Centenarian', 1, 1234, '2024-09-08 18:17:02', '2024-09-08 18:17:02'),
(14, 'DAVAO OCCIDENTAL', 'DON MARCELINO', 'Kalahi CIDSS (KC-NCDDP)', 12, 123, '2024-09-10 18:53:23', '2024-09-10 18:53:23'),
(15, 'DAVAO DE ORO', 'LAAK - 2nd CONGRESSIONAL DISTRICT', 'Disaster Response and Management Program (DRMP)', 1, 1, '2024-09-10 18:54:33', '2024-09-10 18:54:33'),
(16, 'DAVAO OCCIDENTAL', 'JOSE ABAD SANTOS', 'Sustainable Livelihood Program (SLP)', 1, 1, '2024-09-11 13:17:58', '2024-09-11 13:17:58');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_logs`
--

DROP TABLE IF EXISTS `client_logs`;
CREATE TABLE IF NOT EXISTS `client_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_logs_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_logs`
--

INSERT INTO `client_logs` (`id`, `user_id`, `action`, `type`, `record_id`, `created_at`, `updated_at`) VALUES
(1, 9, 'added', 'allocation', 16, '2024-09-11 13:17:58', '2024-09-11 13:17:58'),
(2, 9, 'edited', 'allocation', 5, '2024-09-11 13:18:51', '2024-09-11 13:18:51'),
(3, 9, 'added', 'utilization', 13, '2024-09-11 13:22:14', '2024-09-11 13:22:14'),
(4, 9, 'edited', 'utilization', 5, '2024-09-11 13:22:30', '2024-09-11 13:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_08_01_161502_create_reports_table', 2),
(5, '2024_08_02_161844_create_utilizations_table', 3),
(6, '2024_08_02_162017_create_utilizations_table', 4),
(7, '2024_08_02_164535_create_utilizations_table', 5),
(8, '2024_08_02_175949_create_allocations_table', 6),
(9, '2024_08_02_181730_create_allocations_table', 7),
(10, '2024_08_02_183025_create_allocations_table', 8),
(11, '2024_08_04_133221_create_programs_table', 9),
(12, '2024_08_08_181926_create_admins_table', 10),
(13, '2024_08_16_183557_create_logs_table', 11),
(14, '2024_09_11_181559_create_admin_logs_table', 12),
(15, '2024_09_11_210613_create_client_logs_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `name`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Assistance to Individuals in Crisis Situation (AICS)', 'ppd-images/aics.png', '2024-08-04 09:55:56', '2024-08-28 00:14:55'),
(2, 'Centenarian', 'ppd-images/centenarian.png', '2024-08-04 09:56:24', '2024-08-28 01:12:43'),
(3, 'Disaster Response and Management Program (DRMP)', 'ppd-images/disaster.png', '2024-08-04 09:57:16', '2024-08-27 07:43:40'),
(4, 'Kalahi CIDSS (KC-NCDDP)', 'ppd-images/kalahi.png', '2024-08-04 09:58:07', '2024-08-27 07:13:32'),
(5, 'Pantawid Pamilyang Pilipino Program (4P\'s)', 'ppd-images/4ps.png', '2024-08-04 09:58:55', '2024-08-27 07:06:22'),
(6, 'Social Pension (SocPen) for Indigent Senior Citizen Program', 'ppd-images/socpen.png', '2024-08-04 10:00:13', '2024-08-27 07:14:09'),
(7, 'Supplementary Feeding Program (SFP)', 'ppd-images/sfp.png', '2024-08-04 10:01:05', '2024-08-27 07:14:18'),
(8, 'Sustainable Livelihood Program (SLP)', 'ppd-images/slp.png', '2024-08-04 10:01:46', '2024-08-27 07:14:30'),
(48, 'p', NULL, '2024-09-12 02:02:02', '2024-09-12 02:02:02'),
(47, 'a', NULL, '2024-09-12 01:46:14', '2024-09-12 01:46:14'),
(46, 'd', NULL, '2024-09-11 10:31:14', '2024-09-12 02:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE IF NOT EXISTS `reports` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `province` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `municipality` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allocation` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('NigeI14jm3XqVrHRqIo6A0eroDGS6AL3oK7BVKIt', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiYkFObnU1RGZQMUpMWGF6dGJHWmNSVWM1Yk1PajcyOU5DQnpQVW1BdSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvdXNlcnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo5O30=', 1726108818);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_citymuni`
--

DROP TABLE IF EXISTS `tbl_citymuni`;
CREATE TABLE IF NOT EXISTS `tbl_citymuni` (
  `psgc` varchar(20) NOT NULL,
  `col_citymuni` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `district` varchar(100) NOT NULL,
  `province_psgc` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`psgc`),
  KEY `fk_province_psgc` (`province_psgc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_citymuni`
--

INSERT INTO `tbl_citymuni` (`psgc`, `col_citymuni`, `district`, `province_psgc`) VALUES
('1', '1ST CONGRESSIONAL DISTRICT', '', '1130700000'),
('1102301000', 'ASUNCION', '1', '1102300000'),
('1102303000', 'CARMEN', '2', '1102300000'),
('1102305000', 'KAPALONG ', '1', '1102300000'),
('1102314000', 'NEW CORELLA', '1', '1102300000'),
('1102315000', 'CITY OF PANABO ', '2', '1102300000'),
('1102317000', 'ISLAND GARDEN CITY OF SAMAL', '2', '1102300000'),
('1102318000', 'SANTO TOMAS', '2', '1102300000'),
('1102319000', 'TAGUM CITY', '1', '1102300000'),
('1102322000', 'TALAINGOD ', '1', '1102300000'),
('1102323000', 'BRAULIO DUJALI', '2', '1102300000'),
('1102324000', 'SAN ISIDRO', '1', '1102300000'),
('1102401000', 'BANSALAN', '', '1102400000'),
('1102403000', 'CITY OF DIGOS', '', '1102400000'),
('1102404000', 'HAGONOY', '', '1102400000'),
('1102406000', 'KIBLAWAN', '', '1102400000'),
('1102407000', 'MAGSAYSAY', '', '1102400000'),
('1102408000', 'MALALAG', '', '1102400000'),
('1102410000', 'MATANAO', '', '1102400000'),
('1102411000', 'PADADA', '', '1102400000'),
('1102412000', 'STA.CRUZ', '', '1102400000'),
('1102414000', 'SULOP', '', '1102400000'),
('1102501000', 'BAGANGA', '1', '1102500000'),
('1102502000', 'BANAYBANAY', '2', '1102500000'),
('1102503000', 'BOSTON', '1', '1102500000'),
('1102504000', 'CARAGA', '1', '1102500000'),
('1102505000', 'CATEEL', '1', '1102500000'),
('1102506000', 'GOVERNOR GENEROSO ', '2', '1102500000'),
('1102507000', 'LUPON ', '2', '1102500000'),
('1102508000', 'MANAY', '1', '1102500000'),
('1102509000', 'CITY OF MATI', '2', '1102500000'),
('1102510000', 'SAN ISIDRO ', '2', '1102500000'),
('1102511000', 'TARRAGONA', '1', '1102500000'),
('1108201000', 'COMPOSTELA', '1', '1108200000'),
('1108202000', 'LAAK\r\n', '2', '1108200000'),
('1108203000', 'MABINI\r\n', '2', '1108200000'),
('1108204000', 'MACO', '2', '1108200000'),
('1108205000', 'MARAGUSAN', '1', '1108200000'),
('1108206000', 'MAWAB\r\n', '2', '1108200000'),
('1108207000', 'MONKAYO', '1', '1108200000'),
('1108208000', 'MONTEVISTA', '1', '1108200000'),
('1108209000', 'NABUNTURAN\r\n', '2', '1108200000'),
('1108210000', 'NEW BATAAN', '1', '1108200000'),
('1108211000', 'PANTUKAN\r\n', '2', '1108200000'),
('1108601000', 'DON MARCELINO', '', '1108600000'),
('1108602000', 'JOSE ABAD SANTOS', '', '1108600000'),
('1108603000', 'MALITA', '', '1108600000'),
('1108604000', 'STA.MARIA', '', '1108600000'),
('1108605000', 'SARANGANI', '', '1108600000'),
('2', '2ND CONGRESSIONAL DISTRICT', '', '1130700000'),
('3', '3RD CONGRESSIONAL DISTRICT', '', '1130700000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_province`
--

DROP TABLE IF EXISTS `tbl_province`;
CREATE TABLE IF NOT EXISTS `tbl_province` (
  `psgc` varchar(20) NOT NULL,
  `col_province` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`psgc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_province`
--

INSERT INTO `tbl_province` (`psgc`, `col_province`) VALUES
('1102300000', 'DAVAO DEL NORTE'),
('1102400000', 'DAVAO DEL SUR'),
('1102500000', 'DAVAO ORIENTAL'),
('1108200000', 'DAVAO DE ORO'),
('1108600000', 'DAVAO OCCIDENTAL'),
('1130700000', 'DAVAO CITY');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_region`
--

DROP TABLE IF EXISTS `tbl_region`;
CREATE TABLE IF NOT EXISTS `tbl_region` (
  `psgc` varchar(20) NOT NULL,
  `col_region` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`psgc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_region`
--

INSERT INTO `tbl_region` (`psgc`, `col_region`) VALUES
('110000000', 'REGION XI (DAVAO REGION)');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employeeid` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `otp` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `otp_verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `role`, `email`, `employeeid`, `password`, `program`, `approved`, `active`, `created_at`, `updated_at`, `otp`, `otp_expires_at`, `otp_verified_at`) VALUES
(1, 'adrian', 'O', 'cacho', NULL, 'user', 'acacho@dswd.gov.ph', '11-06067', '$2y$12$kCkic06qyv9QHnuk07iATe5J8Tzv6fOKCGoit3EKGggaFEYzh.fNC', 'Sustainable Livelihood Program (SLP)', 1, 1, '2024-08-15 05:32:30', '2024-09-12 02:27:20', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'Meliza', NULL, 'Facun', NULL, 'admin', 'mf@gmail.com', '11-06068', '$2y$12$p7y1rJUSUOmWecopJajiy.UEmtUe/n5rlOyXYMYFsM.u3ezIeIL2m', 'Social Pension (SocPen) for Indigent Senior Citizen Program', 1, 0, '2024-08-15 05:36:17', '2024-09-12 01:45:11', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'a', NULL, 'a', NULL, 'admin', 'a@dswd.gov.ph', '11-06069', '$2y$12$Jia9Cf0O9PCCWUEzEEqp.OHSpiKRMjHGpLViQ9NglXwWH53u.7ixi', 'Kalahi CIDSS (KC-NCDDP)', 1, 0, '2024-08-15 05:37:21', '2024-09-11 10:01:54', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'az', NULL, 'was', NULL, 'admin', 'mfacun@dswd.gov.ph', '11-999', '$2y$12$de1xfd.Zk3ln2MxS/cC4remo.jZIqiPcIxqXK.5WyjZb0GHwB5bq.', 'Assistance to Individuals in Crisis Situation (AICS)', 1, 0, '2024-08-16 02:03:22', '2024-09-11 10:01:56', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'a', NULL, 'wa', NULL, 'user', 'aa@dswd.gov.ph', '11-990', '$2y$12$zJgmL/LJ8gHajzceVwAyxuqF1eZtnXzYQKcpoqvIciisXppCBdA/m', 'Disaster Response and Management Program (DRMP)', 1, 0, '2024-08-16 02:05:38', '2024-09-12 01:45:36', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'aaa', NULL, 'aaa', NULL, 'user', 'aaa@dswd.gov.ph', '123', '$2y$12$SwSLMg6eZrWiuKE/nZSADu3EzTUEjFu8kR1toheGJ4FrphSQE7gIy', 'Sustainable Livelihood Program (SLP)', 1, 0, '2024-08-16 09:26:07', '2024-09-11 10:20:28', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'm', NULL, 'facun', NULL, 'user', 'mlfacun03187@usep.edu.ph', '1234', '$2y$12$yPIhdxGxHr5JKu3SWAGwR.gQXfdbWeVoQG45KP.nUCAw73o7si/8O', 'Centenarian', 1, 0, '2024-08-18 06:15:09', '2024-09-11 10:10:45', '706663', '2024-08-29 09:35:40', '2024-08-29 09:31:56'),
(9, 'f', NULL, 'm', NULL, 'admin', 'facunmeliza26@gmail.com', '12345', '$2y$12$Vz6vFO12KHylc7Jf.feBM.c/smaCBPvSsDsuaRxlL7gnsG9ERhXyy', 'Kalahi CIDSS (KC-NCDDP)', 1, 1, '2024-08-20 06:20:40', '2024-09-12 01:12:20', '201014', '2024-09-12 09:15:28', '2024-09-12 09:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `utilizations`
--

DROP TABLE IF EXISTS `utilizations`;
CREATE TABLE IF NOT EXISTS `utilizations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_municipality` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `physical` int NOT NULL,
  `fund_utilized` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilizations`
--

INSERT INTO `utilizations` (`id`, `province`, `city_municipality`, `program`, `physical`, `fund_utilized`, `created_at`, `updated_at`) VALUES
(1, 'DAVAO DEL NORTE', 'CARMEN - 2nd CONGRESSIONAL DISTRICT', 'Sustainable Livelihood Program (SLP)', 43, 41341, '2024-08-28 19:50:01', '2024-09-06 20:05:28'),
(2, 'DAVAO DEL SUR', 'MAGSAYSAY', 'Supplementary Feeding Program (SFP)', 51, 44351, '2024-08-28 19:50:13', '2024-09-06 20:59:33'),
(3, 'DAVAO ORIENTAL', 'BAGANGA - 1st CONGRESSIONAL DISTRICT', 'Supplementary Feeding Program (SFP)', 22, 33452, '2024-08-28 19:50:44', '2024-09-06 20:05:09'),
(4, 'DAVAO DE ORO', 'MACO - 2nd CONGRESSIONAL DISTRICT', 'Assistance to Individuals in Crisis Situation (AICS)', 12, 34531, '2024-09-01 15:38:10', '2024-09-02 16:07:37'),
(5, 'DAVAO OCCIDENTAL', 'STA.MARIA', 'Disaster Response and Management Program (DRMP)', 11, 33421, '2024-09-01 15:38:40', '2024-09-11 13:22:30'),
(7, 'DAVAO DEL NORTE', 'ASUNCION - 1st CONGRESSIONAL DISTRICT', 'Centenarian', 1, 2, '2024-09-04 18:02:30', '2024-09-04 18:02:51'),
(6, 'DAVAO CITY', '3RD CONGRESSIONAL DISTRICT', 'Centenarian', 11, 43215, '2024-09-01 16:24:35', '2024-09-01 16:24:35'),
(8, 'DAVAO CITY', '1ST CONGRESSIONAL DISTRICT', 'Assistance to Individuals in Crisis Situation (AICS)', 1, 1, '2024-09-04 18:53:20', '2024-09-04 18:53:20'),
(9, 'DAVAO DEL SUR', 'BANSALAN', 'Assistance to Individuals in Crisis Situation (AICS)', 1, 1, '2024-09-04 20:18:28', '2024-09-04 20:18:28'),
(11, 'DAVAO DEL NORTE', 'KAPALONG - 1st CONGRESSIONAL DISTRICT', 'Sustainable Livelihood Program (SLP)', 12, 11, '2024-09-06 20:02:15', '2024-09-06 20:02:15'),
(12, 'DAVAO DE ORO', 'COMPOSTELA- 1st CONGRESSIONAL DISTRICT', 'Disaster Response and Management Program (DRMP)', 1, 1, '2024-09-08 18:17:38', '2024-09-08 18:17:38'),
(13, 'DAVAO DE ORO', 'MARAGUSAN - 1st CONGRESSIONAL DISTRICT', 'Supplementary Feeding Program (SFP)', 1, 1, '2024-09-11 13:22:14', '2024-09-11 13:22:14');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
