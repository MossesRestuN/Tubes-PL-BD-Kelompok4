<?php
class Bahan extends Model
{
    protected string $table = 'bahan_baku';
    protected string $pk = 'id_bahan';
    protected array $fillable = ['nama_bahan', 'kategori', 'stok', 'stok_minimum', 'satuan', 'vendor'];

    // Batch stok disimpan di riwayat_stok: baris 'awal' dan 'tambah' membawa kolom sisa dan tanggal_kadaluarsa.
    // Kadaluarsa terdekat diambil dari batch yang masih ada sisanya.
    private const SELECT = 'SELECT b.*, (SELECT MIN(x.tanggal_kadaluarsa) FROM riwayat_stok x
                            WHERE x.id_bahan = b.id_bahan AND x.sisa > 0) AS exp_terdekat FROM bahan_baku b';

    public function all(): array { return Database::query(self::SELECT . ' ORDER BY b.nama_bahan')->fetchAll(); }

    // Stok kritis = menipis atau habis
    public function menipis(): array { return Database::query(self::SELECT . ' WHERE b.stok <= b.stok_minimum ORDER BY b.stok')->fetchAll(); }

    // Habis jika stok 0, Menipis jika di bawah atau sama dengan minimum, selain itu Aman
    public static function status(array $b): string
    {
        $stok = (float)$b['stok'];
        if ($stok <= 0) return 'Habis';
        return $stok <= (float)$b['stok_minimum'] ? 'Menipis' : 'Aman';
    }

    // Stok awal bahan baru (stok sudah tersimpan di bahan_baku): satu baris riwayat yang sekaligus menjadi batch pertama
    public function catatAwal(int $idBahan, int $idPengguna, float $jumlah, ?string $kadaluarsa = null): void
    {
        $this->catat($idBahan, $idPengguna, 'awal', $jumlah, 0, $jumlah, 'Stok awal', date('Y-m-d H:i:s'), $jumlah, $kadaluarsa);
    }

    // Pengambilan (stok berkurang) atau penambahan (stok bertambah), lengkap dengan petugas dan waktu kejadian (boleh mundur).
    // - Tambah: batch lama yang sudah kadaluarsa pada tanggal itu dibuang otomatis, lalu baris baru ini menjadi batch baru.
    // - Ambil : diambil dari batch yang paling dulu kadaluarsa (FEFO), batch yang sudah kadaluarsa dilewati.
    // Semua dalam satu transaksi dan baris bahan dikunci agar tidak balapan. Mengembalikan jumlah stok kadaluarsa yang dibuang.
    public function catatGerak(int $idBahan, int $idPengguna, string $jenis, float $jumlah, string $ket, string $waktu, ?string $kadaluarsa = null): float
    {
        $tanggal = substr($waktu, 0, 10);
        return Database::transaction(function () use ($idBahan, $idPengguna, $jenis, $jumlah, $ket, $waktu, $kadaluarsa, $tanggal) {
            $row = Database::query('SELECT stok FROM bahan_baku WHERE id_bahan = :id FOR UPDATE', [':id' => $idBahan])->fetch();
            if (!$row) throw new RuntimeException('Bahan baku tidak ditemukan.');
            $stok = (float)$row['stok'];
            $dibuang = 0.0;

            if ($jenis === 'tambah') {
                $lama = QueryBuilder::table('riwayat_stok')->where('id_bahan', '=', $idBahan)->where('sisa', '>', 0)
                    ->where('tanggal_kadaluarsa', '<', $tanggal)->orderBy('tanggal_kadaluarsa')->get();
                foreach ($lama as $b) {
                    $sisa = (float)$b['sisa'];
                    $baru = max(0, round($stok - $sisa, 2));
                    $this->catat($idBahan, $idPengguna, 'kadaluarsa', $sisa, $stok, $baru,
                        'Otomatis: batch kadaluarsa ' . date('d-m-Y', strtotime($b['tanggal_kadaluarsa'])) . ' dibuang saat stok baru masuk', $waktu);
                    QueryBuilder::table('riwayat_stok')->where('id_riwayat', '=', (int)$b['id_riwayat'])->update(['sisa' => 0]);
                    $stok = $baru;
                    $dibuang += $sisa;
                }
                $sesudah = round($stok + $jumlah, 2);
            } else {
                $sesudah = round($stok - $jumlah, 2);
                if ($sesudah < 0) throw new RuntimeException('Stok tidak cukup. Stok saat ini ' . ($stok + 0) . '.');
                $batch = Database::query('SELECT * FROM riwayat_stok WHERE id_bahan = :id AND sisa > 0
                    ORDER BY (tanggal_kadaluarsa IS NOT NULL AND tanggal_kadaluarsa < :t), (tanggal_kadaluarsa IS NULL), tanggal_kadaluarsa, id_riwayat FOR UPDATE',
                    [':id' => $idBahan, ':t' => $tanggal])->fetchAll();
                $perlu = $jumlah;
                foreach ($batch as $b) {
                    if ($perlu <= 0) break;
                    $ambil = min($perlu, (float)$b['sisa']);
                    QueryBuilder::table('riwayat_stok')->where('id_riwayat', '=', (int)$b['id_riwayat'])->update(['sisa' => round((float)$b['sisa'] - $ambil, 2)]);
                    $perlu = round($perlu - $ambil, 2);
                }
            }

            QueryBuilder::table('bahan_baku')->where('id_bahan', '=', $idBahan)->update(['stok' => $sesudah]);
            $tambah = $jenis === 'tambah'; // stok masuk sekaligus menjadi batch baru
            $this->catat($idBahan, $idPengguna, $jenis, $jumlah, $stok, $sesudah, $ket !== '' ? $ket : null, $waktu, $tambah ? $jumlah : null, $tambah ? $kadaluarsa : null);
            return $dibuang;
        });
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

    // Satu baris riwayat; sisa dan kadaluarsa hanya diisi untuk stok masuk (batch)
    private function catat(int $idBahan, int $idPengguna, string $jenis, float $jumlah, float $sebelum, float $sesudah, ?string $ket, string $waktu, ?float $sisa = null, ?string $kadaluarsa = null): void
    {
        QueryBuilder::table('riwayat_stok')->insert([
            'id_bahan' => $idBahan, 'id_pengguna' => $idPengguna, 'jenis' => $jenis, 'jumlah' => $jumlah,
            'stok_sebelum' => $sebelum, 'stok_sesudah' => $sesudah, 'keterangan' => $ket, 'tanggal' => $waktu,
            'sisa' => $sisa, 'tanggal_kadaluarsa' => $kadaluarsa,
        ]);
    }
}
