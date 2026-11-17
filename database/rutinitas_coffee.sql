
DROP DATABASE IF EXISTS rutinitas_coffee_db;

-- 2. Buat dan gunakan database baru
CREATE DATABASE rutinitas_coffee_db;
USE rutinitas_coffee_db;

-- 3. Tabel Master
CREATE TABLE IF NOT EXISTS Pelanggan (
    id_pelanggan VARCHAR(10) PRIMARY KEY,
    nama_pelanggan VARCHAR(50) NOT NULL,
    nomor_telepon VARCHAR(15),
);

CREATE TABLE IF NOT EXISTS Kasir (
    id_kasir VARCHAR(10) PRIMARY KEY,
    nama_kasir VARCHAR(50) NOT NULL,
    peran VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS Menu (
    id_menu VARCHAR(10) PRIMARY KEY,
    nama_menu VARCHAR(50) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    harga DECIMAL(10,2) NOT NULL CHECK (harga >= 0)
);

CREATE TABLE IF NOT EXISTS Bahan_Baku (
    id_bahan VARCHAR(10) PRIMARY KEY,
    nama_bahan_baku VARCHAR(50) NOT NULL,
    kategori_bahan VARCHAR(30),
    stok DECIMAL(10,2) NOT NULL CHECK (stok >= 0),
    satuan VARCHAR(15) NOT NULL,
    vendor VARCHAR(50)
);

-- 4. Tabel Transaksi Utama (Tanpa catatan_pesanan di awal)
CREATE TABLE IF NOT EXISTS Pesanan (
    id_pesanan VARCHAR(20) PRIMARY KEY,
    id_pelanggan VARCHAR(10) NULL,
    id_kasir VARCHAR(10) NOT NULL,
    tanggal_pesanan DATETIME NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL DEFAULT 0,
    metode_pembayaran VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_pelanggan) REFERENCES Pelanggan(id_pelanggan) ON DELETE SET NULL,
    FOREIGN KEY (id_kasir) REFERENCES Kasir(id_kasir) ON DELETE RESTRICT
);

-- 5. Tabel Penghubung / Detail
CREATE TABLE IF NOT EXISTS Detail_Pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan VARCHAR(20) NOT NULL,
    id_menu VARCHAR(10) NOT NULL,
    jumlah INT NOT NULL CHECK (jumlah > 0),
    subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),
    FOREIGN KEY (id_pesanan) REFERENCES Pesanan(id_pesanan) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS Resep_Menu (
    id_resep INT AUTO_INCREMENT PRIMARY KEY,
    id_menu VARCHAR(10) NOT NULL,
    id_bahan VARCHAR(10) NOT NULL,
    jumlah_kebutuhan DECIMAL(10,2) NOT NULL CHECK (jumlah_kebutuhan > 0),
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu) ON DELETE CASCADE,
    FOREIGN KEY (id_bahan) REFERENCES Bahan_Baku(id_bahan) ON DELETE RESTRICT
);

-- 6. Subbab 4.4: Modifikasi Struktur Tabel menggunakan ALTER TABLE
ALTER TABLE Pesanan 
ADD COLUMN catatan_pesanan VARCHAR(255) NULL;


-- ==========================================================
-- 1. DATABASE & STRUKTUR TABEL (DDL)
-- ==========================================================
DROP DATABASE IF EXISTS rutinitas_coffee_db;
CREATE DATABASE rutinitas_coffee_db;
USE rutinitas_coffee_db;

CREATE TABLE IF NOT EXISTS Pelanggan (
    id_pelanggan VARCHAR(10) PRIMARY KEY,
    nama_pelanggan VARCHAR(50) NOT NULL,
    nomor_telepon VARCHAR(15),
);

CREATE TABLE IF NOT EXISTS Kasir (
    id_kasir VARCHAR(10) PRIMARY KEY,
    nama_kasir VARCHAR(50) NOT NULL,
    shift_kerja VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS Menu (
    id_menu VARCHAR(10) PRIMARY KEY,
    nama_menu VARCHAR(50) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    harga DECIMAL(10,2) NOT NULL CHECK (harga >= 0)
);

CREATE TABLE IF NOT EXISTS Bahan_Baku (
    id_bahan VARCHAR(10) PRIMARY KEY,
    nama_bahan_baku VARCHAR(50) NOT NULL,
    kategori_bahan VARCHAR(30),
    stok DECIMAL(10,2) NOT NULL CHECK (stok >= 0),
    satuan VARCHAR(15) NOT NULL,
    vendor VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS Pesanan (
    id_pesanan VARCHAR(20) PRIMARY KEY,
    id_pelanggan VARCHAR(10) NULL,
    id_kasir VARCHAR(10) NOT NULL,
    tanggal_waktu DATETIME NOT NULL,
    total_keseluruhan DECIMAL(10,2) NOT NULL DEFAULT 0,
    metode_pembayaran VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_pelanggan) REFERENCES Pelanggan(id_pelanggan) ON DELETE SET NULL,
    FOREIGN KEY (id_kasir) REFERENCES Kasir(id_kasir) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS Detail_Pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan VARCHAR(20) NOT NULL,
    id_menu VARCHAR(10) NOT NULL,
    jumlah INT NOT NULL CHECK (jumlah > 0),
    subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),
    FOREIGN KEY (id_pesanan) REFERENCES Pesanan(id_pesanan) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS Resep_Menu (
    id_resep INT AUTO_INCREMENT PRIMARY KEY,
    id_menu VARCHAR(10) NOT NULL,
    id_bahan VARCHAR(10) NOT NULL,
    jumlah_kebutuhan DECIMAL(10,2) NOT NULL CHECK (jumlah_kebutuhan > 0),
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu) ON DELETE CASCADE,
    FOREIGN KEY (id_bahan) REFERENCES Bahan_Baku(id_bahan) ON DELETE RESTRICT
);

-- Modifikasi Tabel (ALTER TABLE)
ALTER TABLE Pesanan 
ADD COLUMN catatan_pesanan VARCHAR(255) NULL;


-- Hapus isi data dari anak ke induk (aman dari foreign key)
DELETE FROM Resep_Menu;
DELETE FROM Detail_Pesanan;
DELETE FROM Pesanan;
DELETE FROM Bahan_Baku;
DELETE FROM Menu;
DELETE FROM Kasir;
DELETE FROM Pelanggan;

-- Masukkan ulang data sampelnya
INSERT INTO Pelanggan VALUES
('PLG001', 'Melin', '081234567890'),
('PLG002', 'Farhan', NULL);

INSERT INTO Kasir VALUES
('KSR001', 'Diaz', 'Pagi'),
('KSR002', 'Vivi', 'Malam');

INSERT INTO Menu VALUES
('MN001', 'Butterscotch Coffee', 'Flavoured', 18000),
('MN002', 'Cona Coffee', 'Signature', 15000),
('MN003', 'Berry Coffee', 'Flavoured', 18000);

INSERT INTO Bahan_Baku VALUES
('BB001', 'Espresso Blend Beans', 'Biji Kopi', 5, 'Kg', 'Supplier A'),
('BB002', 'Fresh Milk / Whole Milk', 'Susu', 8, 'Kotak 1L', 'Supplier B'),
('BB003', 'Sirup Butterscotch', 'Sirup', 2, 'Botol', 'Supplier C');

INSERT INTO Pesanan 
(id_pesanan, id_pelanggan, id_kasir, tanggal_pesanan, total_harga, metode_pembayaran) 
VALUES
('TRX001', 'PLG001', 'KSR001', '2026-10-16 08:01:00', 18000, 'QRIS'),
('TRX002', 'PLG002', 'KSR001', '2026-10-16 08:12:00', 30000, 'QRIS');

INSERT INTO Detail_Pesanan 
(id_pesanan, id_menu, jumlah, subtotal) VALUES
('TRX001', 'MN001', 1, 18000),
('TRX002', 'MN002', 2, 30000);

INSERT INTO Resep_Menu 
(id_menu, id_bahan, jumlah_kebutuhan) VALUES
('MN001', 'BB001', 0.018),
('MN001', 'BB002', 0.120),
('MN001', 'BB003', 0.020);