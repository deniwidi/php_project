
CREATE DATABASE IF NOT EXISTS crud_mahasiswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE crud_mahasiswa;

-- Buat tabel mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id          INT(11)         NOT NULL AUTO_INCREMENT,
    nim         VARCHAR(20)     NOT NULL UNIQUE,
    nama        VARCHAR(100)    NOT NULL,
    jurusan     VARCHAR(100)    NOT NULL,
    angkatan    YEAR            NOT NULL,
    email       VARCHAR(150)    NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Isi data contoh
INSERT INTO mahasiswa (nim, nama, jurusan, angkatan, email) VALUES
('240401010340', 'Deni Widi Alfian',  'Teknik Informatika', 2025, 'deniwidialfian@gmail.com'),
('240401010341', 'Siti Rahayu',       'Sistem Informasi',   2021, 'sitii_sampel@gmail.com'),
('240401010342', 'Budi Santoso',      'Teknik Informasi', 2022, 'budii_sampel@gmail.com'),
('240401010343', 'Dewi Kurniawati',   'Manajemen', 2022, 'dewii_sampel@gmail.comm'),
('240401010344', 'Rizky Firmansyah',  'Komunikasi',   2023, 'rizkyi_sampel@gmail.com');
