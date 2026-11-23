-- Migrasi untuk database yang sudah berjalan (TANPA menghapus data).
-- Jalankan sekali di phpMyAdmin atau HeidiSQL pada database rutinitas_coffee_db.
USE rutinitas_coffee_db;

CREATE TABLE IF NOT EXISTS riwayat_stok (
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

-- Stok yang sudah ada dicatat sebagai stok awal atas nama pengguna pertama
INSERT INTO riwayat_stok (id_bahan, id_pengguna, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, tanggal)
SELECT b.id_bahan, (SELECT MIN(id_pengguna) FROM pengguna), 'awal', b.stok, 0, b.stok, 'Stok awal (migrasi)', NOW()
FROM bahan_baku b
WHERE b.stok > 0 AND NOT EXISTS (SELECT 1 FROM riwayat_stok r WHERE r.id_bahan = b.id_bahan);
