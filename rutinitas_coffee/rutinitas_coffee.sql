-- ==========================================================
-- Rutinitas Coffee: skema database + data awal (satu file, aman dijalankan ulang)
-- Akun login ikut dibuat di sini, jadi tidak perlu menjalankan seed.php lagi.
--   owner    / owner123
--   manajer  / manajer123
--   karyawan / karyawan123
-- ==========================================================
DROP DATABASE IF EXISTS rutinitas_coffee_db;
CREATE DATABASE rutinitas_coffee_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rutinitas_coffee_db;

-- ---------- STRUKTUR TABEL ----------
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(50)  NOT NULL,
    username    VARCHAR(30)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('karyawan','manajer','owner') NOT NULL DEFAULT 'karyawan',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu (
    id_menu   INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(50) NOT NULL,
    kategori  VARCHAR(30) NOT NULL,
    harga     DECIMAL(10,2) NOT NULL CHECK (harga >= 0)
);

CREATE TABLE bahan_baku (
    id_bahan     INT AUTO_INCREMENT PRIMARY KEY,
    nama_bahan   VARCHAR(50) NOT NULL,
    kategori     VARCHAR(30),
    stok         DECIMAL(10,2) NOT NULL CHECK (stok >= 0),
    stok_minimum DECIMAL(10,2) NOT NULL DEFAULT 0,
    satuan       VARCHAR(15) NOT NULL,
    vendor       VARCHAR(50)
);

CREATE TABLE pesanan (
    id_pesanan        INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna       INT NOT NULL,
    nama_pelanggan    VARCHAR(50) NULL,
    tanggal           DATETIME NOT NULL,
    mode_layanan      ENUM('Dine In','Take Away') NOT NULL,
    metode_pembayaran ENUM('Cash','QRIS') NOT NULL,
    total             DECIMAL(12,2) NOT NULL DEFAULT 0,
    catatan           VARCHAR(255) NULL,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE RESTRICT
);

CREATE TABLE detail_pesanan (
    id_detail    INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan   INT NOT NULL,
    id_menu      INT NOT NULL,
    jumlah       INT NOT NULL CHECK (jumlah > 0),
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal     DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
    FOREIGN KEY (id_menu)    REFERENCES menu(id_menu)       ON DELETE RESTRICT
);

-- Riwayat stok: siapa mengambil atau menambah bahan baku, kapan, dan keterangannya
CREATE TABLE riwayat_stok (
    id_riwayat   INT AUTO_INCREMENT PRIMARY KEY,
    id_bahan     INT NOT NULL,
    id_pengguna  INT NOT NULL,
    jenis        ENUM('awal','ambil','tambah') NOT NULL,
    jumlah       DECIMAL(10,2) NOT NULL,
    stok_sebelum DECIMAL(10,2) NOT NULL,
    stok_sesudah DECIMAL(10,2) NOT NULL,
    keterangan   VARCHAR(255) NULL,
    tanggal      DATETIME NOT NULL,
    FOREIGN KEY (id_bahan)    REFERENCES bahan_baku(id_bahan) ON DELETE CASCADE,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE RESTRICT
);

-- ---------- DATA AWAL ----------
-- Password disimpan sebagai hash bcrypt (kompatibel dengan password_verify PHP)
INSERT INTO pengguna (nama, username, password, role) VALUES
('Owner Rutinitas', 'owner',    '$2y$10$9A88fK86T7Y5oXA/NELNJe95TqSTIpocpcnf67TVcUk11roQtudfW',    'owner'),
('Manajer Shift',   'manajer',  '$2y$10$bRTJMFR8lsf/ZcdcOy0Np.c6LgN2ltrP/6/xm7JsiH/oHk2q/b23a',  'manajer'),
('Karyawan Kasir',  'karyawan', '$2y$10$jGKz9ua.rBjrQ/KYPk52O.Ff/oWvjcIZ0xyZuuzoOu4VdLUoeJkIS', 'karyawan');

INSERT INTO menu (nama_menu, kategori, harga) VALUES
('Butterscotch Coffee', 'Coffee',     18000),
('Cona Coffee',         'Coffee',     15000),
('Berry Coffee',        'Coffee',     18000),
('Chocolate',           'Non-Coffee', 16000);

INSERT INTO bahan_baku (nama_bahan, kategori, stok, stok_minimum, satuan, vendor) VALUES
('Espresso Blend Beans', 'Biji Kopi', 5, 2,  'Kg',       'Supplier A'),
('Fresh Milk',           'Susu',      8, 10, 'Kotak 1L', 'Supplier B'),
('Sirup Butterscotch',   'Sirup',     2, 1,  'Botol',    'Supplier C');

-- Contoh pesanan (tanggal hari ini agar dashboard dan laporan langsung terisi)
INSERT INTO pesanan (id_pengguna, nama_pelanggan, tanggal, mode_layanan, metode_pembayaran, total) VALUES
(3, 'Melin',  NOW(), 'Dine In',   'QRIS', 18000),
(3, 'Farhan', NOW(), 'Take Away', 'QRIS', 30000);

INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) VALUES
(1, 1, 1, 18000, 18000),
(2, 2, 2, 15000, 30000);

-- Riwayat stok awal untuk bahan contoh (dicatat atas nama owner)
INSERT INTO riwayat_stok (id_bahan, id_pengguna, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, tanggal)
SELECT id_bahan, 1, 'awal', stok, 0, stok, 'Stok awal', NOW() FROM bahan_baku;