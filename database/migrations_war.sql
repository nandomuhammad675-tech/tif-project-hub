-- ============================================
-- TIF Hub - Project War / Klaim Tiket
-- mysql -u root -p tif_hub < database/migrations_war.sql
-- ============================================
SET NAMES utf8mb4;
USE tif_hub;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'projects'
      AND COLUMN_NAME = 'is_claimable'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE projects ADD COLUMN is_claimable TINYINT(1) NOT NULL DEFAULT 1 AFTER is_published',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS war_settings (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    is_open             TINYINT(1) NOT NULL DEFAULT 0,
    starts_at           DATETIME NULL,
    ends_at             DATETIME NULL,
    active_semester_id  INT UNSIGNED NULL,
    updated_by          INT UNSIGNED NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (active_semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project_claims (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_code     VARCHAR(32) NOT NULL,
    project_id      INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    semester_id     INT UNSIGNED NOT NULL,
    claimed_at      DATETIME NOT NULL,
    status          ENUM('claimed', 'revoked') NOT NULL DEFAULT 'claimed',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ticket_code (ticket_code),
    UNIQUE KEY uq_project_semester (project_id, semester_id),
    UNIQUE KEY uq_user_semester (user_id, semester_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE RESTRICT,
    INDEX idx_user (user_id),
    INDEX idx_semester (semester_id),
    INDEX idx_claimed_at (claimed_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS claim_violations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    project_id      INT UNSIGNED NULL,
    semester_id     INT UNSIGNED NOT NULL,
    attempted_at    DATETIME NOT NULL,
    reason          VARCHAR(255) NOT NULL DEFAULT 'Percobaan klaim kedua pada semester yang sama',
    admin_note      TEXT NULL,
    penalty_points  DECIMAL(5,2) NULL,
    status          ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by     INT UNSIGNED NULL,
    reviewed_at     DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE RESTRICT,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user_sem (user_id, semester_id)
) ENGINE=InnoDB;

INSERT INTO war_settings (is_open, starts_at, ends_at, active_semester_id, updated_by)
SELECT 0, NULL, NULL, s.id, 1
FROM semesters s
WHERE s.code = 'S1'
  AND NOT EXISTS (SELECT 1 FROM war_settings LIMIT 1)
LIMIT 1;

INSERT INTO war_settings (is_open, starts_at, ends_at, active_semester_id, updated_by)
SELECT 0, NULL, NULL, (SELECT id FROM semesters ORDER BY id LIMIT 1), 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM war_settings LIMIT 1);
