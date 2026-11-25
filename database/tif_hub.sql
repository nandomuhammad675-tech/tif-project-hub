-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2026 at 01:49 PM
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
-- Database: `tif_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `type` enum('project','ticket','both') NOT NULL DEFAULT 'project',
  `icon` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `icon`, `created_at`) VALUES
(1, 'Website', 'website', 'project', '🌐', '2026-11-23 09:38:06'),
(2, 'Mobile App', 'mobile-app', 'project', '📱', '2026-11-23 09:38:06'),
(3, 'Desktop', 'desktop', 'project', '💻', '2026-11-23 09:38:06'),
(4, 'IoT', 'iot', 'project', '🔌', '2026-11-23 09:38:06'),
(5, 'AI', 'ai', 'project', '🤖', '2026-11-23 09:38:06'),
(6, 'Game', 'game', 'project', '🎮', '2026-11-23 09:38:06'),
(7, 'Sistem Informasi', 'sistem-informasi', 'project', '📊', '2026-11-23 09:38:06'),
(8, 'Lainnya', 'lainnya', 'both', '📦', '2026-11-23 09:38:06'),
(9, 'Akademik', 'akademik', 'ticket', '📚', '2026-11-23 09:38:06'),
(10, 'Project', 'project', 'ticket', '🛠️', '2026-11-23 09:38:06'),
(11, 'Repository', 'repository', 'ticket', '📂', '2026-11-23 09:38:06'),
(12, 'Laboratorium', 'laboratorium', 'ticket', '🔬', '2026-11-23 09:38:06'),
(13, 'Infrastruktur', 'infrastruktur', 'ticket', '🖥️', '2026-11-23 09:38:06');

-- --------------------------------------------------------

--
-- Table structure for table `claim_violations`
--

CREATE TABLE `claim_violations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED DEFAULT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `attempted_at` datetime NOT NULL,
  `reason` varchar(255) NOT NULL DEFAULT 'Percobaan klaim kedua pada semester yang sama',
  `admin_note` text DEFAULT NULL,
  `penalty_points` decimal(5,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text NOT NULL,
  `short_desc` varchar(300) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `semester_id` int(10) UNSIGNED DEFAULT NULL,
  `course_name` varchar(150) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `technologies` varchar(500) DEFAULT NULL,
  `github_url` varchar(500) DEFAULT NULL,
  `demo_url` varchar(500) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `is_claimable` tinyint(1) NOT NULL DEFAULT 1,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `slug`, `description`, `short_desc`, `thumbnail`, `category_id`, `semester_id`, `course_name`, `academic_year`, `technologies`, `github_url`, `demo_url`, `is_published`, `is_claimable`, `view_count`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Sistem Perpustakaan Digital', 'sistem-perpustakaan-digital', 'Aplikasi web untuk mengelola koleksi buku, peminjaman, dan pengembalian di perpustakaan kampus. Dilengkapi fitur pencarian advanced, notifikasi jatuh tempo, dan dashboard statistik.', 'Aplikasi web manajemen perpustakaan kampus dengan fitur peminjaman online.', NULL, 1, 3, 'Pemrograman Web', '2024/2025', 'PHP,MySQL,JavaScript,Bootstrap', 'https://github.com/example/perpustakaan-digital', 'https://demo.example.com/perpus', 1, 1, 129, 1, '2026-11-23 09:38:06', '2026-11-23 12:35:31'),
(2, 'Aplikasi Kasir UMKM', 'aplikasi-kasir-umkm', 'Sistem point of sale sederhana untuk usaha mikro. Mendukung multi-produk, laporan penjualan harian, dan export Excel.', 'POS sederhana untuk UMKM dengan laporan penjualan.', NULL, 7, 3, 'Pemrograman Web', '2024/2025', 'PHP,MySQL,JavaScript,Chart.js', 'https://github.com/example/kasir-umkm', NULL, 1, 1, 89, 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(3, 'Smart Home IoT Controller', 'smart-home-iot-controller', 'Proyek IoT untuk mengontrol perangkat rumah tangga (lampu, kipas, AC) melalui aplikasi web dan sensor suhu/kelembaban.', 'Kontrol perangkat rumah via web + sensor IoT.', NULL, 4, 5, 'Internet of Things', '2023/2024', 'Arduino,ESP32,PHP,MQTT,JavaScript', 'https://github.com/example/smart-home-iot', NULL, 1, 1, 156, 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(4, 'Chatbot Akademik dengan AI', 'chatbot-akademik-ai', 'Chatbot berbasis NLP untuk menjawab pertanyaan seputar jadwal kuliah, syarat akademik, dan informasi kampus.', 'Chatbot AI untuk informasi akademik mahasiswa.', NULL, 5, 5, 'Kecerdasan Buatan', '2023/2024', 'Python,Flask,PHP,JavaScript,OpenAI API', 'https://github.com/example/chatbot-akademik', 'https://demo.example.com/chatbot', 1, 1, 210, 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(5, 'Game Edukasi Matematika', 'game-edukasi-matematika', 'Game interaktif berbasis web untuk membantu anak-anak belajar operasi hitung dasar dengan sistem skor dan level.', 'Game web edukasi matematika untuk anak-anak.', NULL, 6, 2, 'Pemrograman Game', '2025/2026', 'JavaScript,HTML5 Canvas,CSS3', 'https://github.com/example/math-game', 'https://demo.example.com/mathgame', 1, 1, 73, 1, '2026-11-23 09:38:06', '2026-11-25 10:58:54'),
(6, 'Aplikasi Monitoring Absensi', 'aplikasi-monitoring-absensi', 'Sistem absensi mahasiswa berbasis QR Code dengan laporan kehadiran real-time untuk dosen.', 'Absensi mahasiswa via QR Code + laporan real-time.', NULL, 7, 4, 'Rekayasa Perangkat Lunak', '2024/2025', 'PHP,MySQL,JavaScript,QRCode.js', 'https://github.com/example/absensi-qr', NULL, 1, 1, 94, 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(7, 'Mobile App Katalog Produk', 'mobile-app-katalog-produk', 'Aplikasi Android untuk menampilkan katalog produk UMKM lokal dengan fitur favorit dan share.', 'Aplikasi Android katalog produk UMKM.', NULL, 2, 4, 'Pemrograman Mobile', '2024/2025', 'Kotlin,Firebase,XML', 'https://github.com/example/katalog-umkm', NULL, 1, 1, 45, 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(8, 'Sistem Inventaris Lab', 'sistem-inventaris-lab', 'Aplikasi desktop untuk mengelola inventaris peralatan laboratorium komputer, termasuk peminjaman alat.', 'Manajemen inventaris lab komputer (desktop).', NULL, 3, 3, 'Pemrograman Desktop', '2024/2025', 'Java,JavaFX,MySQL', 'https://github.com/example/inventaris-lab', NULL, 1, 1, 52, 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06');

-- --------------------------------------------------------

--
-- Table structure for table `project_claims`
--

CREATE TABLE `project_claims` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_code` varchar(32) NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `claimed_at` datetime NOT NULL,
  `status` enum('claimed','revoked') NOT NULL DEFAULT 'claimed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_claims`
--

INSERT INTO `project_claims` (`id`, `ticket_code`, `project_id`, `user_id`, `semester_id`, `claimed_at`, `status`, `created_at`) VALUES
(1, 'CLAIM-2026-E03705', 3, 2, 1, '2026-11-23 16:17:13', 'claimed', '2026-11-23 16:17:13');

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

CREATE TABLE `project_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_in_project` varchar(50) DEFAULT 'Anggota',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `role_in_project`, `created_at`) VALUES
(1, 1, 2, 'Ketua', '2026-11-23 09:38:06'),
(2, 1, 3, 'Anggota', '2026-11-23 09:38:06'),
(3, 2, 2, 'Ketua', '2026-11-23 09:38:06'),
(4, 3, 4, 'Ketua', '2026-11-23 09:38:06'),
(5, 3, 5, 'Anggota', '2026-11-23 09:38:06'),
(6, 4, 2, 'Ketua', '2026-11-23 09:38:06'),
(7, 4, 3, 'Anggota', '2026-11-23 09:38:06'),
(8, 5, 6, 'Ketua', '2026-11-23 09:38:06'),
(9, 6, 4, 'Ketua', '2026-11-23 09:38:06'),
(10, 6, 5, 'Anggota', '2026-11-23 09:38:06'),
(11, 7, 5, 'Ketua', '2026-11-23 09:38:06'),
(12, 8, 4, 'Ketua', '2026-11-23 09:38:06'),
(13, 3, 2, 'Ketua', '2026-11-23 16:17:13'),
(14, 1, 7, 'Anggota', '2026-11-25 13:53:23');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `code`, `academic_year`, `created_at`) VALUES
(1, 'Semester 1', 'S1', '2025/2026', '2026-11-23 09:38:06'),
(2, 'Semester 2', 'S2', '2025/2026', '2026-11-23 09:38:06'),
(3, 'Semester 3', 'S3', '2024/2025', '2026-11-23 09:38:06'),
(4, 'Semester 4', 'S4', '2024/2025', '2026-11-23 09:38:06'),
(5, 'Semester 5', 'S5', '2023/2024', '2026-11-23 09:38:06'),
(6, 'Semester 6', 'S6', '2023/2024', '2026-11-23 09:38:06'),
(7, 'Semester 7', 'S7', '2022/2023', '2026-11-23 09:38:06'),
(8, 'Semester 8', 'S8', '2022/2023', '2026-11-23 09:38:06');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('rendah','normal','tinggi') NOT NULL DEFAULT 'normal',
  `status` enum('open','diproses','selesai','ditolak') NOT NULL DEFAULT 'open',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_code`, `user_id`, `category_id`, `title`, `description`, `priority`, `status`, `assigned_to`, `created_at`, `updated_at`, `closed_at`) VALUES
(1, 'TIF-001', 2, 11, 'Permintaan Repository Project', 'Mohon dibuatkan repository GitHub untuk proyek Sistem Perpustakaan Digital kelompok kami.', 'normal', 'diproses', 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06', NULL),
(2, 'TIF-002', 3, 9, 'Jadwal Ujian Tengah Semester', 'Mohon informasi terkait jadwal UTS semester ini untuk mata kuliah Pemrograman Web.', 'tinggi', 'open', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06', NULL),
(3, 'TIF-003', 4, 12, 'Akses Laboratorium Komputer', 'Saya membutuhkan akses lab komputer di luar jam kuliah untuk menyelesaikan proyek IoT.', 'normal', 'selesai', 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06', NULL),
(4, 'TIF-004', 2, 13, 'Gangguan WiFi di Lab', 'WiFi di laboratorium lantai 2 sering terputus sejak kemarin.', 'tinggi', 'open', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06', NULL),
(5, 'TIF-005', 5, 10, 'Review Kode Project', 'Mohon review dan feedback untuk proyek Monitoring Absensi kami sebelum presentasi.', 'rendah', 'ditolak', 1, '2026-11-23 09:38:06', '2026-11-23 09:38:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL,
  `reply_id` int(10) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_replies`
--

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `is_internal`, `created_at`) VALUES
(1, 1, 2, 'Halo Admin, kami sudah siapkan struktur folder project-nya. Mohon bantuannya.', 0, '2026-11-23 09:38:06'),
(2, 1, 1, 'Baik, repository sedang diproses. Akan kami kabari setelah selesai dibuat.', 0, '2026-11-23 09:38:06'),
(3, 3, 4, 'Saya sudah mengisi formulir peminjaman lab.', 0, '2026-11-23 09:38:06'),
(4, 3, 1, 'Akses sudah diberikan. Silakan gunakan kartu mahasiswa Anda untuk masuk.', 0, '2026-11-23 09:38:06'),
(5, 5, 1, 'Mohon maaf, saat ini kami belum bisa melakukan review kode secara mendalam karena keterbatasan waktu. Silakan konsultasi langsung dengan dosen pengampu.', 0, '2026-11-23 09:38:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','admin') NOT NULL DEFAULT 'mahasiswa',
  `class` varchar(20) DEFAULT NULL,
  `semester` tinyint(3) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nim`, `name`, `email`, `password`, `role`, `class`, `semester`, `status`, `avatar`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Administrator', 'admin@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'admin', NULL, NULL, 'active', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(2, 'E41252273', 'Muhammad Vernando Susanto', 'nandomuhammad675@gmail.com', '$2y$10$aJPhtyvl5rjuQRjbo8V69.wriyGJrrGpeRerZOTgvPfM0eBm3BQfi', 'mahasiswa', 'TI-3A', 5, 'active', 'u2ffeab1f6ff357334a4c3f46.jpg', '2026-11-23 09:38:06', '2026-11-25 10:20:49'),
(3, '2021002', 'Siti Nurhaliza', 'siti@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-3A', 5, 'active', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(4, '2022001', 'Budi Santoso', 'budi@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-2B', 3, 'active', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(5, '2022002', 'Dewi Lestari', 'dewi@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-2B', 3, 'active', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(6, '2023001', 'Eko Prasetyo', 'eko@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-1A', 1, 'active', NULL, '2026-11-23 09:38:06', '2026-11-23 09:38:06'),
(7, 'E4125009', 'ARIEF UDAH UGM', 'ariefugm@gmail.com', '$2y$10$DcGZ/v25HfEgBsiEYB7Oceif4Vn/f4a1iVa1V.5aTxCLyx/75aywq', 'mahasiswa', 'C', 3, 'active', NULL, '2026-11-25 11:01:00', '2026-11-25 11:01:00'),
(8, '2333', 'fffff', 'dasd@ffdff.com', '$2y$10$uDbA3qi/RdfuHDrV5jiQjOCk3Uzf4q9NGlf/w4CPW7cUI5Rigv0Ua', 'mahasiswa', NULL, 1, 'active', NULL, '2026-11-25 11:57:30', '2026-11-25 11:57:30'),
(9, 'E41256738', 'dicky ade novendy', 'dicky@gmail.com', '$2y$10$/MEZomiTAfvSPMwHUuXkVuiV0A3GfthLA8kaAeg.4i0o1G9ocN7qO', 'mahasiswa', NULL, 2, 'active', NULL, '2026-11-25 19:31:37', '2026-11-25 19:31:37');

-- --------------------------------------------------------

--
-- Table structure for table `war_settings`
--

CREATE TABLE `war_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `active_semester_id` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `war_settings`
--

INSERT INTO `war_settings` (`id`, `is_open`, `starts_at`, `ends_at`, `active_semester_id`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 0, '2026-11-23 16:00:00', NULL, 1, 1, '2026-11-23 09:38:14', '2026-11-25 10:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `project_id`, `created_at`) VALUES
(1, 2, 5, '2026-11-23 16:08:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `claim_violations`
--
ALTER TABLE `claim_violations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_sem` (`user_id`,`semester_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_published` (`is_published`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_semester` (`semester_id`);

--
-- Indexes for table `project_claims`
--
ALTER TABLE `project_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket_code` (`ticket_code`),
  ADD UNIQUE KEY `uq_project_semester` (`project_id`,`semester_id`),
  ADD UNIQUE KEY `uq_user_semester` (`user_id`,`semester_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_semester` (`semester_id`),
  ADD KEY `idx_claimed_at` (`claimed_at`);

--
-- Indexes for table `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_code` (`ticket_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_priority` (`priority`);

--
-- Indexes for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `reply_id` (`reply_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `war_settings`
--
ALTER TABLE `war_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active_semester_id` (`active_semester_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_project` (`user_id`,`project_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `claim_violations`
--
ALTER TABLE `claim_violations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_claims`
--
ALTER TABLE `project_claims`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `war_settings`
--
ALTER TABLE `war_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claim_violations`
--
ALTER TABLE `claim_violations`
  ADD CONSTRAINT `claim_violations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claim_violations_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `claim_violations_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`),
  ADD CONSTRAINT `claim_violations_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_claims`
--
ALTER TABLE `project_claims`
  ADD CONSTRAINT `project_claims_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_claims_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_claims_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`);

--
-- Constraints for table `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `project_images_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `ticket_attachments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_attachments_ibfk_2` FOREIGN KEY (`reply_id`) REFERENCES `ticket_replies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_attachments_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `war_settings`
--
ALTER TABLE `war_settings`
  ADD CONSTRAINT `war_settings_ibfk_1` FOREIGN KEY (`active_semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `war_settings_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
