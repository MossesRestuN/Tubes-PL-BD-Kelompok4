<?php
$own = $role === 'karyawan';
$thn = date('Y');
// Empat kartu ringkas: angka utama + keterangan kecil di bawahnya
$cards = [
    ['Penjualan Hari Ini', rupiah($r['hari_t']),  'Bulan ini: ' . rupiah($r['bulan_t']),            'wallet'],
    ['Total Penjualan',    rupiah($r['semua_t']), 'Tahun ' . $thn . ': ' . rupiah($r['tahun_t']),   'chart'],
    ['Pesanan Hari Ini',   (int)$r['hari_n'],     'Bulan ini: ' . (int)$r['bulan_n'] . ' pesanan',   'cart'],
];
if (isset($stokMenipis)) $cards[] = ['Stok Kritis', count($stokMenipis), 'Bahan baku hampir habis', 'alert'];
else $cards[] = ['Seluruh Pesanan', (int)$r['semua_n'], 'Tahun ' . $thn . ': ' . (int)$r['tahun_n'] . ' pesanan', 'list'];
?>
<div class="head">
  <div><h1 class="page-title">Dashboard</h1><p class="muted">Halo, <?= e(Auth::user()['nama']) ?>.<?= $own ? ' Angka di bawah hanya pesanan yang Anda input.' : '' ?></p></div>
  <a class="btn" href="<?= url('pesanan/create') ?>">+ Pesanan Baru</a>
</div>

<div class="stats">
  <?php foreach ($cards as [$t, $v, $s, $i]): ?>
    <div class="kpi">
      <div class="kpi-top"><span class="kpi-label"><?= e($t) ?></span><span class="kpi-ico"><?= icon($i) ?></span></div>
      <div class="kpi-val"><?= e($v) ?></div>
      <div class="kpi-sub"><?= e($s) ?><?php if ($t === 'Stok Kritis' && $v > 0): ?> <span class="badge">Stok Kritis</span><?php endif; ?></div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (isset($stokMenipis)): ?>
<div class="charts">
  <div class="card">
    <h3>Grafik Penjualan Per Bulan Di Tahun Ini</h3>
    <?= bar_chart(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], $perBulan, ['#94A3B8', '#DC2626']) ?>
    <div class="legend"><span style="--c:#94A3B8">Cash</span><span style="--c:#DC2626">QRIS</span></div>
  </div>
  <div class="card">
    <h3>Grafik Penjualan Per Tahun</h3>
    <?= bar_chart($perTahun['labels'], $perTahun['series'], ['#94A3B8', '#DC2626']) ?>
    <div class="legend"><span style="--c:#94A3B8">Cash</span><span style="--c:#DC2626">QRIS</span></div>
  </div>
</div>

<div class="card">
  <h3>Bahan baku hampir habis</h3>
  <?php if (!$stokMenipis): ?><p class="muted">Semua stok masih aman.</p><?php else: ?>
  <table><thead><tr><th>Bahan</th><th>Stok</th><th>Minimum</th><th>Vendor</th><th></th></tr></thead><tbody>
  <?php foreach ($stokMenipis as $b): ?>
    <tr>
      <td><?= e($b['nama_bahan']) ?></td>
      <td><?php if ((float)$b['stok'] <= 0): ?><span class="pill habis">Habis</span><?php else: ?><span class="pill"><?= e($b['stok'] + 0) ?> <?= e($b['satuan']) ?></span><?php endif; ?></td>
      <td><?= e($b['stok_minimum'] + 0) ?></td>
      <td><?= e($b['vendor']) ?></td>
      <td class="actions"><a class="link-action" href="<?= url('bahan') ?>">Restock &rarr;</a></td>
    </tr>
  <?php endforeach; ?></tbody></table>
  <?php endif; ?>
</div>
<?php endif; ?>
