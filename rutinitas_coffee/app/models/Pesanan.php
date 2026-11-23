<?php
class Pesanan extends Model
{
    protected string $table = 'pesanan';
    protected string $pk = 'id_pesanan';

    public function daftar(?int $userId): array
    {
        $sql = 'SELECT p.*, u.nama AS kasir FROM pesanan p JOIN pengguna u ON u.id_pengguna = p.id_pengguna';
        $bind = [];
        if ($userId !== null) { $sql .= ' WHERE p.id_pengguna = :u'; $bind[':u'] = $userId; }
        return Database::query($sql . ' ORDER BY p.tanggal DESC, p.id_pesanan DESC LIMIT 100', $bind)->fetchAll();    }

    public function detail(int $id): ?array
    {
        $h = Database::query('SELECT p.*, u.nama AS kasir FROM pesanan p JOIN pengguna u ON u.id_pengguna = p.id_pengguna WHERE p.id_pesanan = :id', [':id' => $id])->fetch();
        if (!$h) return null;
        $items = Database::query('SELECT d.*, m.nama_menu FROM detail_pesanan d JOIN menu m ON m.id_menu = d.id_menu WHERE d.id_pesanan = :id', [':id' => $id])->fetchAll();
        return ['header' => $h, 'items' => $items];
    }

    // Simpan header dan detail dalam satu transaksi; harga selalu diambil dari database
    public function buat(array $h, array $items): int
    {
        $pdo = Database::connect();
        try {
            $pdo->beginTransaction();
            $menus = [];
            foreach ($items as $idMenu => $qty) {
                $m = QueryBuilder::table('menu')->where('id_menu', '=', $idMenu)->first();
                if (!$m) throw new RuntimeException('Menu tidak ditemukan.');
                $menus[$idMenu] = $m;
            }
            $id = QueryBuilder::table('pesanan')->insert([
                'id_pengguna'       => $h['id_pengguna'],
                'nama_pelanggan'    => $h['nama_pelanggan'] !== '' ? $h['nama_pelanggan'] : null,
                'tanggal'           => date('Y-m-d H:i:s'),
                'mode_layanan'      => $h['mode_layanan'],
                'metode_pembayaran' => $h['metode_pembayaran'],
                'total'             => 0,
                'catatan'           => $h['catatan'] !== '' ? $h['catatan'] : null,
            ]);
            $total = 0;
            foreach ($items as $idMenu => $qty) {
                $harga = (float)$menus[$idMenu]['harga'];
                $sub = $harga * $qty;
                $total += $sub;
                QueryBuilder::table('detail_pesanan')->insert([
                    'id_pesanan' => $id, 'id_menu' => $idMenu, 'jumlah' => $qty,
                    'harga_satuan' => $harga, 'subtotal' => $sub,
                ]);
            }
            QueryBuilder::table('pesanan')->where('id_pesanan', '=', $id)->update(['total' => $total]);
            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public function ringkasanHariIni(?int $userId): array
    {
        $sql = 'SELECT COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS total FROM pesanan WHERE DATE(tanggal) = CURDATE()';
        $bind = [];
        if ($userId !== null) { $sql .= ' AND id_pengguna = :u'; $bind[':u'] = $userId; }
        return Database::query($sql, $bind)->fetch();
    }

    public function harian(string $a, string $b): array
    {
        return Database::query('SELECT DATE(tanggal) AS hari, COUNT(*) AS jumlah, SUM(total) AS total FROM pesanan WHERE DATE(tanggal) BETWEEN :a AND :b GROUP BY DATE(tanggal) ORDER BY hari DESC', [':a' => $a, ':b' => $b])->fetchAll();
    }

    public function terlaris(string $a, string $b): array
    {
        return Database::query('SELECT m.nama_menu, SUM(d.jumlah) AS qty, SUM(d.subtotal) AS omzet FROM detail_pesanan d JOIN pesanan p ON p.id_pesanan = d.id_pesanan JOIN menu m ON m.id_menu = d.id_menu WHERE DATE(p.tanggal) BETWEEN :a AND :b GROUP BY m.id_menu, m.nama_menu ORDER BY qty DESC LIMIT 10', [':a' => $a, ':b' => $b])->fetchAll();
    }

    public function perKasir(string $a, string $b): array
    {
        return Database::query('SELECT u.nama, u.role, COUNT(*) AS jumlah, SUM(p.total) AS total FROM pesanan p JOIN pengguna u ON u.id_pengguna = p.id_pengguna WHERE DATE(p.tanggal) BETWEEN :a AND :b GROUP BY u.id_pengguna, u.nama, u.role ORDER BY total DESC', [':a' => $a, ':b' => $b])->fetchAll();
    }
    
    // Ringkasan hari ini, bulan ini, tahun ini, dan seluruhnya (jumlah pesanan dan total penjualan)
    public function ringkasan(?int $userId): array
    {
        $w = $userId !== null ? ' WHERE id_pengguna = :u' : '';
        $bind = $userId !== null ? [':u' => $userId] : [];
        $hari  = 'DATE(tanggal) = CURDATE()';
        $bulan = 'YEAR(tanggal) = YEAR(CURDATE()) AND MONTH(tanggal) = MONTH(CURDATE())';
        $tahun = 'YEAR(tanggal) = YEAR(CURDATE())';
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN $hari THEN 1 END), 0) AS hari_n,
            COALESCE(SUM(CASE WHEN $hari THEN total END), 0) AS hari_t,
            COALESCE(SUM(CASE WHEN $bulan THEN 1 END), 0) AS bulan_n,
            COALESCE(SUM(CASE WHEN $bulan THEN total END), 0) AS bulan_t,
            COALESCE(SUM(CASE WHEN $tahun THEN 1 END), 0) AS tahun_n,
            COALESCE(SUM(CASE WHEN $tahun THEN total END), 0) AS tahun_t,
            COUNT(*) AS semua_n,
            COALESCE(SUM(total), 0) AS semua_t
            FROM pesanan$w";
        return Database::query($sql, $bind)->fetch();
    }

    // Penjualan per bulan di satu tahun, dipisah Cash dan QRIS
    public function perBulan(int $tahun): array
    {
        $rows = Database::query('SELECT MONTH(tanggal) AS k, metode_pembayaran AS m, SUM(total) AS t FROM pesanan WHERE YEAR(tanggal) = :y GROUP BY MONTH(tanggal), metode_pembayaran', [':y' => $tahun])->fetchAll();
        $out = ['Cash' => array_fill(0, 12, 0), 'QRIS' => array_fill(0, 12, 0)];
        foreach ($rows as $r) $out[$r['m']][(int)$r['k'] - 1] = (float)$r['t'];
        return $out;
    }

    // Penjualan per tahun, dipisah Cash dan QRIS
    public function perTahun(): array
    {
        $rows = Database::query('SELECT YEAR(tanggal) AS k, metode_pembayaran AS m, SUM(total) AS t FROM pesanan GROUP BY YEAR(tanggal), metode_pembayaran')->fetchAll();
        $years = [(int)date('Y')];
        foreach ($rows as $r) $years[] = (int)$r['k'];
        $years = array_values(array_unique($years));
        sort($years);
        $out = ['Cash' => array_fill(0, count($years), 0), 'QRIS' => array_fill(0, count($years), 0)];
        foreach ($rows as $r) $out[$r['m']][array_search((int)$r['k'], $years, true)] = (float)$r['t'];
        return ['labels' => $years, 'series' => $out];
    }
}
