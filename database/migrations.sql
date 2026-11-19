-- ============================================
-- TIF Hub - Full Database Schema
-- PHP 8.4 + MySQL + PDO
-- ============================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS tif_hub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tif_hub;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS ticket_attachments;
DROP TABLE IF EXISTS ticket_replies;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS project_images;
DROP TABLE IF EXISTS project_members;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS semesters;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. users
-- ============================================
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nim         VARCHAR(20)  NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('mahasiswa', 'admin') NOT NULL DEFAULT 'mahasiswa',
    class       VARCHAR(20)  NULL,
    semester    TINYINT UNSIGNED NULL,
    status      ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    avatar      VARCHAR(255) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- 2. categories
-- ============================================
CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    slug        VARCHAR(80) NOT NULL UNIQUE,
    type        ENUM('project', 'ticket', 'both') NOT NULL DEFAULT 'project',
    icon        VARCHAR(50) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 3. semesters
-- ============================================
CREATE TABLE semesters (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    code        VARCHAR(20) NOT NULL UNIQUE,
    academic_year VARCHAR(20) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 4. projects
-- ============================================
CREATE TABLE projects (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200) NOT NULL,
    slug            VARCHAR(220) NOT NULL UNIQUE,
    description     TEXT NOT NULL,
    short_desc      VARCHAR(300) NULL,
    thumbnail       VARCHAR(255) NULL,
    category_id     INT UNSIGNED NULL,
    semester_id     INT UNSIGNED NULL,
    course_name     VARCHAR(150) NULL,
    academic_year   VARCHAR(20) NULL,
    technologies    VARCHAR(500) NULL,
    github_url      VARCHAR(500) NULL,
    demo_url        VARCHAR(500) NULL,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_published (is_published),
    INDEX idx_category (category_id),
    INDEX idx_semester (semester_id)
) ENGINE=InnoDB;

-- ============================================
-- 4b. project_images (galeri foto proyek)
-- ============================================
CREATE TABLE project_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  INT UNSIGNED NOT NULL,
    filename    VARCHAR(255) NOT NULL,
    is_cover    TINYINT(1) NOT NULL DEFAULT 0,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id)
) ENGINE=InnoDB;

-- ============================================
-- 5. project_members
-- ============================================
CREATE TABLE project_members (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    role_in_project VARCHAR(50) DEFAULT 'Anggota',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member (project_id, user_id)
) ENGINE=InnoDB;

-- ============================================
-- 6. tickets
-- ============================================
CREATE TABLE tickets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_code     VARCHAR(20) NOT NULL UNIQUE,
    user_id         INT UNSIGNED NOT NULL,
    category_id     INT UNSIGNED NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT NOT NULL,
    priority        ENUM('rendah', 'normal', 'tinggi') NOT NULL DEFAULT 'normal',
    status          ENUM('open', 'diproses', 'selesai', 'ditolak') NOT NULL DEFAULT 'open',
    assigned_to     INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at       DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_priority (priority)
) ENGINE=InnoDB;

-- ============================================
-- 7. ticket_replies
-- ============================================
CREATE TABLE ticket_replies (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    message     TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 8. ticket_attachments
-- ============================================
CREATE TABLE ticket_attachments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT UNSIGNED NOT NULL,
    reply_id    INT UNSIGNED NULL,
    file_name   VARCHAR(255) NOT NULL,
    file_path   VARCHAR(500) NOT NULL,
    file_size   INT UNSIGNED NULL,
    mime_type   VARCHAR(100) NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_id) REFERENCES ticket_replies(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- Password semua akun: password123
-- ============================================

INSERT INTO users (nim, name, email, password, role, class, semester, status) VALUES
(NULL, 'Administrator', 'admin@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'admin', NULL, NULL, 'active'),
('2021001', 'Ahmad Rizki', 'mahasiswa@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-3A', 5, 'active'),
('2021002', 'Siti Nurhaliza', 'siti@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-3A', 5, 'active'),
('2022001', 'Budi Santoso', 'budi@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-2B', 3, 'active'),
('2022002', 'Dewi Lestari', 'dewi@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-2B', 3, 'active'),
('2023001', 'Eko Prasetyo', 'eko@tifhub.local', '$2y$10$T9BRb8dwv68Yhe4mcxFAIuBBkwjch9SsESafOKzNJiLPvrgZMuAHG', 'mahasiswa', 'TI-1A', 1, 'active');

INSERT INTO categories (name, slug, type, icon) VALUES
('Website', 'website', 'project', '🌐'),
('Mobile App', 'mobile-app', 'project', '📱'),
('Desktop', 'desktop', 'project', '💻'),
('IoT', 'iot', 'project', '🔌'),
('AI', 'ai', 'project', '🤖'),
('Game', 'game', 'project', '🎮'),
('Sistem Informasi', 'sistem-informasi', 'project', '📊'),
('Lainnya', 'lainnya', 'both', '📦'),
('Akademik', 'akademik', 'ticket', '📚'),
('Project', 'project', 'ticket', '🛠️'),
('Repository', 'repository', 'ticket', '📂'),
('Laboratorium', 'laboratorium', 'ticket', '🔬'),
('Infrastruktur', 'infrastruktur', 'ticket', '🖥️');

INSERT INTO semesters (name, code, academic_year) VALUES
('Semester 1', 'S1', '2025/2026'),
('Semester 2', 'S2', '2025/2026'),
('Semester 3', 'S3', '2024/2025'),
('Semester 4', 'S4', '2024/2025'),
('Semester 5', 'S5', '2023/2024'),
('Semester 6', 'S6', '2023/2024'),
('Semester 7', 'S7', '2022/2023'),
('Semester 8', 'S8', '2022/2023');

INSERT INTO projects (title, slug, description, short_desc, category_id, semester_id, course_name, academic_year, technologies, github_url, demo_url, is_published, view_count, created_by) VALUES
('Sistem Perpustakaan Digital', 'sistem-perpustakaan-digital',
 'Aplikasi web untuk mengelola koleksi buku, peminjaman, dan pengembalian di perpustakaan kampus. Dilengkapi fitur pencarian advanced, notifikasi jatuh tempo, dan dashboard statistik.',
 'Aplikasi web manajemen perpustakaan kampus dengan fitur peminjaman online.',
 1, 3, 'Pemrograman Web', '2024/2025', 'PHP,MySQL,JavaScript,Bootstrap',
 'https://github.com/example/perpustakaan-digital', 'https://demo.example.com/perpus', 1, 128, 1),

('Aplikasi Kasir UMKM', 'aplikasi-kasir-umkm',
 'Sistem point of sale sederhana untuk usaha mikro. Mendukung multi-produk, laporan penjualan harian, dan export Excel.',
 'POS sederhana untuk UMKM dengan laporan penjualan.',
 7, 3, 'Pemrograman Web', '2024/2025', 'PHP,MySQL,JavaScript,Chart.js',
 'https://github.com/example/kasir-umkm', NULL, 1, 89, 1),

('Smart Home IoT Controller', 'smart-home-iot-controller',
 'Proyek IoT untuk mengontrol perangkat rumah tangga (lampu, kipas, AC) melalui aplikasi web dan sensor suhu/kelembaban.',
 'Kontrol perangkat rumah via web + sensor IoT.',
 4, 5, 'Internet of Things', '2023/2024', 'Arduino,ESP32,PHP,MQTT,JavaScript',
 'https://github.com/example/smart-home-iot', NULL, 1, 156, 1),

('Chatbot Akademik dengan AI', 'chatbot-akademik-ai',
 'Chatbot berbasis NLP untuk menjawab pertanyaan seputar jadwal kuliah, syarat akademik, dan informasi kampus.',
 'Chatbot AI untuk informasi akademik mahasiswa.',
 5, 5, 'Kecerdasan Buatan', '2023/2024', 'Python,Flask,PHP,JavaScript,OpenAI API',
 'https://github.com/example/chatbot-akademik', 'https://demo.example.com/chatbot', 1, 210, 1),

('Game Edukasi Matematika', 'game-edukasi-matematika',
 'Game interaktif berbasis web untuk membantu anak-anak belajar operasi hitung dasar dengan sistem skor dan level.',
 'Game web edukasi matematika untuk anak-anak.',
 6, 2, 'Pemrograman Game', '2025/2026', 'JavaScript,HTML5 Canvas,CSS3',
 'https://github.com/example/math-game', 'https://demo.example.com/mathgame', 1, 67, 1),

('Aplikasi Monitoring Absensi', 'aplikasi-monitoring-absensi',
 'Sistem absensi mahasiswa berbasis QR Code dengan laporan kehadiran real-time untuk dosen.',
 'Absensi mahasiswa via QR Code + laporan real-time.',
 7, 4, 'Rekayasa Perangkat Lunak', '2024/2025', 'PHP,MySQL,JavaScript,QRCode.js',
 'https://github.com/example/absensi-qr', NULL, 1, 94, 1),

('Mobile App Katalog Produk', 'mobile-app-katalog-produk',
 'Aplikasi Android untuk menampilkan katalog produk UMKM lokal dengan fitur favorit dan share.',
 'Aplikasi Android katalog produk UMKM.',
 2, 4, 'Pemrograman Mobile', '2024/2025', 'Kotlin,Firebase,XML',
 'https://github.com/example/katalog-umkm', NULL, 1, 45, 1),

('Sistem Inventaris Lab', 'sistem-inventaris-lab',
 'Aplikasi desktop untuk mengelola inventaris peralatan laboratorium komputer, termasuk peminjaman alat.',
 'Manajemen inventaris lab komputer (desktop).',
 3, 3, 'Pemrograman Desktop', '2024/2025', 'Java,JavaFX,MySQL',
 'https://github.com/example/inventaris-lab', NULL, 1, 52, 1);

INSERT INTO project_members (project_id, user_id, role_in_project) VALUES
(1, 2, 'Ketua'), (1, 3, 'Anggota'),
(2, 2, 'Ketua'),
(3, 4, 'Ketua'), (3, 5, 'Anggota'),
(4, 2, 'Ketua'), (4, 3, 'Anggota'),
(5, 6, 'Ketua'),
(6, 4, 'Ketua'), (6, 5, 'Anggota'),
(7, 5, 'Ketua'),
(8, 4, 'Ketua');

INSERT INTO tickets (ticket_code, user_id, category_id, title, description, priority, status, assigned_to) VALUES
('TIF-001', 2, 11, 'Permintaan Repository Project', 'Mohon dibuatkan repository GitHub untuk proyek Sistem Perpustakaan Digital kelompok kami.', 'normal', 'diproses', 1),
('TIF-002', 3, 9, 'Jadwal Ujian Tengah Semester', 'Mohon informasi terkait jadwal UTS semester ini untuk mata kuliah Pemrograman Web.', 'tinggi', 'open', NULL),
('TIF-003', 4, 12, 'Akses Laboratorium Komputer', 'Saya membutuhkan akses lab komputer di luar jam kuliah untuk menyelesaikan proyek IoT.', 'normal', 'selesai', 1),
('TIF-004', 2, 13, 'Gangguan WiFi di Lab', 'WiFi di laboratorium lantai 2 sering terputus sejak kemarin.', 'tinggi', 'open', NULL),
('TIF-005', 5, 10, 'Review Kode Project', 'Mohon review dan feedback untuk proyek Monitoring Absensi kami sebelum presentasi.', 'rendah', 'ditolak', 1);

INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal) VALUES
(1, 2, 'Halo Admin, kami sudah siapkan struktur folder project-nya. Mohon bantuannya.', 0),
(1, 1, 'Baik, repository sedang diproses. Akan kami kabari setelah selesai dibuat.', 0),
(3, 4, 'Saya sudah mengisi formulir peminjaman lab.', 0),
(3, 1, 'Akses sudah diberikan. Silakan gunakan kartu mahasiswa Anda untuk masuk.', 0),
(5, 1, 'Mohon maaf, saat ini kami belum bisa melakukan review kode secara mendalam karena keterbatasan waktu. Silakan konsultasi langsung dengan dosen pengampu.', 0);
