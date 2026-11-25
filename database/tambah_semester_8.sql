-- Pastikan Semester 8 ada di tabel semesters (aman dijalankan berulang kali).
-- Jalankan di phpMyAdmin (tab SQL) pada database tif_hub.
INSERT INTO `semesters` (`name`, `code`, `academic_year`)
SELECT 'Semester 8', 'S8', '2022/2023' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `semesters` WHERE `code` = 'S8' OR `name` = 'Semester 8'
);
