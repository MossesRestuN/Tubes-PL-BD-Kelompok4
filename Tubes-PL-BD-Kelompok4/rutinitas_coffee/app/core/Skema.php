<?php
// Menyesuaikan database lama ke skema terbaru (6 tabel: batch stok digabung ke riwayat_stok, status menu Kosong).
// Dijalankan sekali per sesi login dan aman diulang; database baru dari rutinitas_coffee.sql sudah sesuai.
class Skema
{
    public static function pastikan(): void
    {
        if (!empty($_SESSION['skema_v4'])) return;

        // Riwayat stok sekaligus menyimpan batch: baris 'awal' dan 'tambah' membawa sisa stok dan tanggal kadaluarsa
        $tabelBaru = !self::tabelAda('riwayat_stok');
        Database::query("CREATE TABLE IF NOT EXISTS riwayat_stok (
            id_riwayat         INT AUTO_INCREMENT PRIMARY KEY,
            id_bahan           INT NOT NULL,
            id_pengguna        INT NOT NULL,
            jenis              ENUM('awal','ambil','tambah','kadaluarsa') NOT NULL,
            jumlah             DECIMAL(10,2) NOT NULL,
            stok_sebelum       DECIMAL(10,2) NOT NULL,
            stok_sesudah       DECIMAL(10,2) NOT NULL,
            keterangan         VARCHAR(255) NULL,
            tanggal            DATETIME NOT NULL,
            sisa               DECIMAL(10,2) NULL,
            tanggal_kadaluarsa DATE NULL,
            FOREIGN KEY (id_bahan)    REFERENCES bahan_baku(id_bahan) ON DELETE CASCADE,
            FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE RESTRICT
        )");
        $tipe = self::tipeKolom('riwayat_stok', 'jenis');
        if ($tipe !== null && strpos($tipe, 'kadaluarsa') === false) {
            Database::query("ALTER TABLE riwayat_stok MODIFY jenis ENUM('awal','ambil','tambah','kadaluarsa') NOT NULL");
        }
        $pindah = $tabelBaru;
        if (self::tipeKolom('riwayat_stok', 'sisa') === null) {
            Database::query('ALTER TABLE riwayat_stok ADD COLUMN sisa DECIMAL(10,2) NULL, ADD COLUMN tanggal_kadaluarsa DATE NULL');
            $pindah = true;
        }
        if ($pindah) self::pindahkanStok();

        // Status menu: Tersedia atau Kosong (sebelumnya Habis)
        $tipe = self::tipeKolom('menu', 'status_ketersediaan');
        if ($tipe === null) {
            Database::query("ALTER TABLE menu ADD COLUMN status_ketersediaan ENUM('Tersedia','Kosong') NOT NULL DEFAULT 'Tersedia'");
        } elseif (strpos($tipe, 'Habis') !== false) {
            Database::query("ALTER TABLE menu MODIFY status_ketersediaan ENUM('Tersedia','Habis','Kosong') NOT NULL DEFAULT 'Tersedia'");
            Database::query("UPDATE menu SET status_ketersediaan = 'Kosong' WHERE status_ketersediaan = 'Habis'");
            Database::query("ALTER TABLE menu MODIFY status_ketersediaan ENUM('Tersedia','Kosong') NOT NULL DEFAULT 'Tersedia'");
        }

        $_SESSION['skema_v4'] = true;
    }

    // Stok yang sudah ada dijadikan batch di riwayat_stok (dari tabel bahan_batch versi 7 tabel, atau dari bahan_baku untuk database yang lebih lama).
    // Tabel bahan_batch lama tidak dihapus supaya datanya tetap aman, tetapi tidak dipakai lagi.
    private static function pindahkanStok(): void
    {
        $u = Database::query("SELECT id_pengguna FROM pengguna ORDER BY (role = 'owner') DESC, id_pengguna LIMIT 1")->fetchColumn();
        if ($u === false) return;
        $kolom = 'id_bahan, id_pengguna, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, tanggal, sisa, tanggal_kadaluarsa';
        if (self::tabelAda('bahan_batch')) {
            Database::query("INSERT INTO riwayat_stok ($kolom)
                SELECT x.id_bahan, :u, 'awal', x.jumlah_sisa, b.stok, b.stok, 'Pindahan batch lama', x.tanggal_masuk, x.jumlah_sisa, x.tanggal_kadaluarsa
                FROM bahan_batch x JOIN bahan_baku b ON b.id_bahan = x.id_bahan WHERE x.jumlah_sisa > 0", [':u' => $u]);
        } else {
            $exp = self::tipeKolom('bahan_baku', 'tanggal_kadaluarsa') !== null ? 'tanggal_kadaluarsa' : 'NULL';
            Database::query("INSERT INTO riwayat_stok ($kolom)
                SELECT id_bahan, :u, 'awal', stok, stok, stok, 'Pindahan stok lama', NOW(), stok, $exp FROM bahan_baku WHERE stok > 0", [':u' => $u]);
        }
    }

    private static function tipeKolom(string $tabel, string $kolom): ?string
    {
        $t = Database::query('SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c',
            [':t' => $tabel, ':c' => $kolom])->fetchColumn();
        return $t === false ? null : (string)$t;
    }

    private static function tabelAda(string $tabel): bool
    {
        return (bool)Database::query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t', [':t' => $tabel])->fetchColumn();
    }
}
