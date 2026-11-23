<?php
$bln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$own = $role === 'karyawan' ? ' (milik saya)' : '';
$cards = [
    ['Penjualan Hari Ini',   rupiah($r['hari_t']),  date('d-m-Y'),                 'g1'],
    ['Penjualan Bulan Ini',  rupiah($r['bulan_t']), $bln[(int)date('n')],          'g2'],
    ['Penjualan Tahun Ini',  rupiah($r['tahun_t']), date('Y'),                     'g3'],
    ['Seluruh Penjualan',    rupiah($r['semua_t']), 'Semua',                       'g4'],
    ['Pesanan Hari Ini',     (int)$r['hari_n'],     date('d-m-Y'),                 'g5'],
    ['Pesanan Bulan Ini',    (int)$r['bulan_n'],    $bln[(int)date('n')],          'g6'],
    ['Pesanan Tahun Ini',    (int)$r['tahun_n'],    date('Y'),                     'g7'],
    ['Seluruh Pesanan',      (int)$r['semua_n'],    'Semua',                       'g8'],
];
?>
<div class="head">
  <div><h1 class="page-title">Dashboard</h1><p class="muted">Halo, <?= e(Auth::user()['nama']) ?>.<?= $own ? ' Angka di bawah hanya pesanan yang Anda input.' : '' ?></p></div>
  <a class="btn" href="<?= url('pesanan/create') ?>">+ Pesanan Baru</a>
</div>

<div class="stats">
  <?php foreach ($cards as [$t, $v, $s, $g]): ?>
    <div class="stat <?= $g ?>"><h4><?= e($t) ?></h4><div class="val"><?= e($v) ?></div><small><?= e($s . $own) ?></small></div>
  <?php endforeach; ?>
</div>

<?php if (isset($stokMenipis)): ?>
<div class="stats mini">
  <div class="card minicard"><small>Bahan baku hampir habis</small><div class="val"><?= count($stokMenipis) ?></div></div>
  <?php if ($role === 'owner'): ?><div class="card minicard"><small>Akun pengguna</small><div class="val"><?= (int)$jumlahUser ?></div></div><?php endif; ?>
</div>

<div class="charts">
  <div class="card">
    <h3>Grafik Penjualan Per Bulan Di Tahun Ini</h3>
    <?= bar_chart(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], $perBulan, ['#E30220', '#252024']) ?>
    <div class="legend"><span style="--c:#E30220">Cash</span><span style="--c:#252024">QRIS</span></div>
  </div>
  <div class="card">
    <h3>Grafik Penjualan Per Tahun</h3>
    <?= bar_chart($perTahun['labels'], $perTahun['series'], ['#E30220', '#252024']) ?>
    <div class="legend"><span style="--c:#E30220">Cash</span><span style="--c:#252024">QRIS</span></div>
  </div>
</div>

<div class="card">
  <h3>Bahan baku hampir habis</h3>
  <?php if (!$stokMenipis): ?><p class="muted">Semua stok masih aman.</p><?php else: ?>
  <table><thead><tr><th>Bahan</th><th>Stok</th><th>Minimum</th><th>Vendor</th></tr></thead><tbody>
  <?php foreach ($stokMenipis as $b): ?>
    <tr><td><?= e($b['nama_bahan']) ?></td><td><?= e($b['stok'] + 0) ?> <?= e($b['satuan']) ?></td><td><?= e($b['stok_minimum'] + 0) ?></td><td><?= e($b['vendor']) ?></td></tr>
  <?php endforeach; ?></tbody></table>
  <?php endif; ?>
</div>
<?php endif; ?>