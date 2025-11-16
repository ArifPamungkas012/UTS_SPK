-- =====================================================================
--   SQL FILE : ahp_kos_FIXED.sql
--   DATABASE AHP SISTEM PEMILIHAN KOS (STRUKTUR DIPERBAIKI)
-- =====================================================================

-- -----------------------------
-- 1. CREATE DATABASE
-- -----------------------------
CREATE DATABASE IF NOT EXISTS ahp_kos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE ahp_kos;

-- -----------------------------
-- 2. TABLE: KRITERIA
-- -----------------------------
CREATE TABLE IF NOT EXISTS kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- -----------------------------
-- 3. TABLE: ALTERNATIF
-- -----------------------------
CREATE TABLE IF NOT EXISTS alternatif (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ===============================================================
-- 4. TABLE: PERBANDINGAN KRITERIA (STRUKTUR BARU)
--    Primary Key: (kriteria1, kriteria2) untuk update data PHP
-- ===============================================================
CREATE TABLE IF NOT EXISTS perbandingan_kriteria (
    kriteria1 INT NOT NULL,
    kriteria2 INT NOT NULL,
    nilai FLOAT NOT NULL,
    PRIMARY KEY (kriteria1, kriteria2), -- PK gabungan
    FOREIGN KEY (kriteria1) REFERENCES kriteria(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (kriteria2) REFERENCES kriteria(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ===============================================================
-- 5. TABLE: PERBANDINGAN ALTERNATIF PER KRITERIA (STRUKTUR BARU)
--    Primary Key: (kriteria_id, alt1, alt2) untuk update data PHP
-- ===============================================================
CREATE TABLE IF NOT EXISTS perbandingan_alternatif (
    kriteria_id INT NOT NULL,
    alt1 INT NOT NULL,
    alt2 INT NOT NULL,
    nilai FLOAT NOT NULL,
    PRIMARY KEY (kriteria_id, alt1, alt2), -- PK gabungan
    FOREIGN KEY (kriteria_id) REFERENCES kriteria(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (alt1) REFERENCES alternatif(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (alt2) REFERENCES alternatif(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 6. INSERT DATA KRITERIA & ALTERNATIF
-- -----------------------------------------------------------
INSERT INTO kriteria (id, kode, nama) VALUES
(1, 'C1', 'Harga'),
(2, 'C2', 'Jarak ke Kampus'),
(3, 'C3', 'Fasilitas'),
(4, 'C4', 'Keamanan'),
(5, 'C5', 'Kebersihan');

INSERT INTO alternatif (id, kode, nama) VALUES
(1, 'K1', 'Kos A (Dekat Kampus)'),
(2, 'K2', 'Kos B (Fasilitas Lengkap)'),
(3, 'K3', 'Kos C (Paling Murah)'),
(4, 'K4', 'Kos D (Sangat Bersih)'),
(5, 'K5', 'Kos E (Lingkungan Tenang)');

-- ===========================================================
-- 7. PERBANDINGAN KRITERIA (LENGKAP dengan nilai kebalikan)
-- ===========================================================

-- Nilai input (Diagonal Atas)
INSERT INTO perbandingan_kriteria (kriteria1, kriteria2, nilai) VALUES
(1, 2, 3), (1, 3, 2), (1, 4, 1), (1, 5, 2),
(2, 3, 0.5), (2, 4, 0.33), (2, 5, 0.5),
(3, 4, 0.25), (3, 5, 3),
(4, 5, 2);

-- Nilai Kebalikan (Diagonal Bawah)
INSERT INTO perbandingan_kriteria (kriteria1, kriteria2, nilai) VALUES
(2, 1, 1/3), (3, 1, 1/2), (4, 1, 1/1), (5, 1, 1/2),
(3, 2, 1/0.5), (4, 2, 1/0.33), (5, 2, 1/0.5),
(4, 3, 1/0.25), (5, 3, 1/3),
(5, 4, 1/2);


-- ===========================================================
-- 8. PERBANDINGAN ALTERNATIF UNTUK SETIAP KRITERIA (LENGKAP)
--    (Menggunakan format INSERT INTO yang berpasangan)
-- ===========================================================

-- C1: Harga
INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai) VALUES
(1,1,2,1), (1,2,1,1/1), -- K1 vs K2
(1,1,3,0.33), (1,3,1,1/0.33), -- K1 vs K3
(1,1,4,3), (1,4,1,1/3), -- K1 vs K4
(1,1,5,2), (1,5,1,1/2), -- K1 vs K5

(1,2,3,0.33), (1,3,2,1/0.33), -- K2 vs K3
(1,2,4,3), (1,4,2,1/3), -- K2 vs K4
(1,2,5,2), (1,5,2,1/2), -- K2 vs K5

(1,3,4,5), (1,4,3,1/5), -- K3 vs K4
(1,3,5,4), (1,5,3,1/4), -- K3 vs K5

(1,4,5,1), (1,5,4,1/1); -- K4 vs K5

-- C2: Jarak
INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai) VALUES
(2,1,2,1), (2,2,1,1/1),
(2,1,3,3), (2,3,1,1/3),
(2,1,4,5), (2,4,1,1/5),
(2,1,5,4), (2,5,1,1/4),

(2,2,3,3), (2,3,2,1/3),
(2,2,4,5), (2,4,2,1/5),
(2,2,5,4), (2,5,2,1/4),

(2,3,4,0.5), (2,4,3,1/0.5),
(2,3,5,0.33), (2,5,3,1/0.33),

(2,4,5,0.5), (2,5,4,1/0.5);

-- C3: Fasilitas
INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai) VALUES
(3,1,2,0.2), (3,2,1,1/0.2),
(3,1,3,0.5), (3,3,1,1/0.5),
(3,1,4,0.33), (3,4,1,1/0.33),
(3,1,5,3), (3,5,1,1/3),

(3,2,3,3), (3,3,2,1/3),
(3,2,4,4), (3,4,2,1/4),
(3,2,5,5), (3,5,2,1/5),

(3,3,4,0.5), (3,4,3,1/0.5),
(3,3,5,2), (3,5,3,1/2),

(3,4,5,3), (3,5,4,1/3);

-- C4: Keamanan
INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai) VALUES
(4,1,2,0.25), (4,2,1,1/0.25),
(4,1,3,1), (4,3,1,1/1),
(4,1,4,0.5), (4,4,1,1/0.5),
(4,1,5,0.2), (4,5,1,1/0.2),

(4,2,3,5), (4,3,2,1/5),
(4,2,4,3), (4,4,2,1/3),
(4,2,5,2), (4,5,2,1/2),

(4,3,4,2), (4,4,3,1/2),
(4,3,5,1), (4,5,3,1/1),

(4,4,5,0.5), (4,5,4,1/0.5);

-- C5: Kebersihan
INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai) VALUES
(5,1,2,1), (5,2,1,1/1),
(5,1,3,2), (5,3,1,1/2),
(5,1,4,0.25), (5,4,1,1/0.25),
(5,1,5,1), (5,5,1,1/1),

(5,2,3,2), (5,3,2,1/2),
(5,2,4,0.2), (5,4,2,1/0.2),
(5,2,5,1), (5,5,2,1/1),

(5,3,4,0.14), (5,4,3,1/0.14),
(5,3,5,0.5), (5,5,3,1/0.5),

(5,4,5,3), (5,5,4,1/3);

-- ==========================
-- SQL FILE SELESAI
-- ==========================