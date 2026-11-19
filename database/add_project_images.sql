-- ============================================
-- Upgrade: galeri foto proyek
-- Jalankan sekali jika database tif_hub sudah ada:
--   mysql -u root -p tif_hub < database/add_project_images.sql
-- ============================================
SET NAMES utf8mb4;
USE tif_hub;

CREATE TABLE IF NOT EXISTS project_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  INT UNSIGNED NOT NULL,
    filename    VARCHAR(255) NOT NULL,
    is_cover    TINYINT(1) NOT NULL DEFAULT 0,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id)
) ENGINE=InnoDB;
