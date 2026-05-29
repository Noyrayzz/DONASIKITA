CREATE DATABASE IF NOT EXISTS db_donasi CHARACTER SET utf8mb4;
USE db_donasi;

-- DROP biar selalu fresh (PENTING)
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS campaigns;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','donatur') DEFAULT 'donatur',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE campaigns (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    user_id     INT NOT NULL,
    judul       VARCHAR(200) NOT NULL,
    slug        VARCHAR(220) NOT NULL UNIQUE,
    deskripsi   TEXT NOT NULL,
    gambar      VARCHAR(255) DEFAULT 'default.jpg',
    target_dana DECIMAL(15,2) DEFAULT 0,
    terkumpul   DECIMAL(15,2) DEFAULT 0,
    tgl_mulai   DATE NOT NULL,
    tgl_selesai DATE NOT NULL,
    status      ENUM('aktif','selesai','dibatalkan') DEFAULT 'aktif',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE donations (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id  INT NOT NULL,
    user_id      INT DEFAULT NULL,
    nama_donatur VARCHAR(100) DEFAULT 'Hamba Allah',
    jumlah       DECIMAL(15,2) NOT NULL,
    pesan        TEXT,
    metode       ENUM('GoPay','OVO','DANA','Transfer') DEFAULT 'Transfer',
    status       ENUM('sukses','pending','gagal') DEFAULT 'sukses',
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT DEFAULT NULL,
    aksi       VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- SEED DATA
INSERT INTO users (nama, email, password, role) VALUES
('Admin', 'admin@donasi.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO categories (nama, slug) VALUES
('Pendidikan', 'pendidikan'),
('Kesehatan', 'kesehatan'),
('Bencana Alam', 'bencana-alam'),
('Yatim Piatu', 'yatim-piatu'),
('Masjid & TPQ', 'masjid-tpq');

INSERT INTO campaigns
(category_id, user_id, judul, slug, deskripsi, target_dana, terkumpul, tgl_mulai, tgl_selesai)
VALUES
(1, 1, 'Beasiswa Anak Yatim Berprestasi', 'beasiswa-anak-yatim', 'Program beasiswa untuk anak-anak yatim berprestasi yang tidak mampu melanjutkan pendidikan karena keterbatasan biaya.', 10000000, 6500000, '2026-01-01', '2026-12-31'),
(2, 1, 'Operasi Jantung Pak Slamet', 'operasi-jantung-pak-slamet', 'Pak Slamet, 58 tahun, seorang petani dari Sidoarjo, membutuhkan operasi jantung segera. Biaya operasi sangat mahal.', 50000000, 32000000, '2026-02-01', '2026-07-30'),
(3, 1, 'Bantuan Korban Banjir Porong', 'bantuan-banjir-porong', 'Banjir bandang melanda Kecamatan Porong. Ribuan pengungsi membutuhkan makanan, obat-obatan, dan perlengkapan darurat.', 25000000, 21000000, '2026-04-01', '2026-06-30');
