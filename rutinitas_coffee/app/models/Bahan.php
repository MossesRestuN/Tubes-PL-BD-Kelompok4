<?php
class Bahan extends Model
{
    protected string $table = 'bahan_baku';
    protected string $pk = 'id_bahan';
    protected array $fillable = ['nama_bahan', 'kategori', 'stok', 'stok_minimum', 'satuan', 'vendor'];

    // Membuat tabel riwayat jika belum ada (database lama yang belum dimigrasi)
    public function pastikanTabel(): void
    {
        Database::query("CREATE TABLE IF NOT EXISTS riwayat_stok (
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
        )");
    }

    public function all(): array { return $this->qb()->orderBy('nama_bahan')->get(); }

    public function menipis(): array
    {
        return Database::query('SELECT * FROM bahan_baku WHERE stok <= stok_minimum ORDER BY stok')->fetchAll();
    }

    // Catat stok awal saat bahan baru dibuat (stok sudah tersimpan di bahan_baku)
    public function catatAwal(int $idBahan, int $idPengguna, float $jumlah): void
    {
        QueryBuilder::table('riwayat_stok')->insert([
            'id_bahan' => $idBahan, 'id_pengguna' => $idPengguna, 'jenis' => 'awal',
            'jumlah' => $jumlah, 'stok_sebelum' => 0, 'stok_sesudah' => $jumlah,
            'keterangan' => 'Stok awal', 'tanggal' => date('Y-m-d H:i:s'),
        ]);
    }

    // Pengambilan (stok berkurang) atau penambahan (stok bertambah) beserta nama petugas.
    // Stok dan riwayat disimpan dalam satu transaksi, baris bahan dikunci agar tidak balapan.
    public function catatGerak(int $idBahan, int $idPengguna, string $jenis, float $jumlah, string $ket): void
    {
        if (!in_array($jenis, ['ambil', 'tambah'], true)) throw new InvalidArgumentException('Jenis tidak valid.');
        $pdo = Database::connect();
        try {
            $pdo->beginTransaction();
            $row = Database::query('SELECT stok FROM bahan_baku WHERE id_bahan = :id FOR UPDATE', [':id' => $idBahan])->fetch();
            if (!$row) throw new RuntimeException('Bahan baku tidak ditemukan.');
            $sebelum = (float)$row['stok'];
            $sesudah = round($jenis === 'ambil' ? $sebelum - $jumlah : $sebelum + $jumlah, 2);
            if ($sesudah < 0) throw new RuntimeException('Stok tidak cukup. Stok saat ini ' . ($sebelum + 0) . '.');
            QueryBuilder::table('bahan_baku')->where('id_bahan', '=', $idBahan)->update(['stok' => $sesudah]);
            QueryBuilder::table('riwayat_stok')->insert([
                'id_bahan' => $idBahan, 'id_pengguna' => $idPengguna, 'jenis' => $jenis,
                'jumlah' => $jumlah, 'stok_sebelum' => $sebelum, 'stok_sesudah' => $sesudah,
                'keterangan' => $ket !== '' ? $ket : null, 'tanggal' => date('Y-m-d H:i:s'),
            ]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    // Riwayat semua bahan atau satu bahan, terbaru di atas
    public function riwayat(?int $idBahan = null, int $limit = 200, array $jenis = []): array
    {
        $sql = 'SELECT r.*, b.nama_bahan, b.satuan, u.nama AS petugas, u.role
                FROM riwayat_stok r
                JOIN bahan_baku b ON b.id_bahan = r.id_bahan
                JOIN pengguna u ON u.id_pengguna = r.id_pengguna';
        $bind = []; $w = [];
        if ($idBahan !== null) { $w[] = 'r.id_bahan = :b'; $bind[':b'] = $idBahan; }
        if ($jenis) {
            $ph = [];
            foreach (array_values($jenis) as $i => $j) { $ph[] = ":j$i"; $bind[":j$i"] = $j; }
            $w[] = 'r.jenis IN (' . implode(', ', $ph) . ')';
        }
        if ($w) $sql .= ' WHERE ' . implode(' AND ', $w);
        return Database::query($sql . ' ORDER BY r.tanggal DESC, r.id_riwayat DESC LIMIT ' . (int)$limit, $bind)->fetchAll();
    }
}
