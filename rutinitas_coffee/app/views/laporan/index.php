<h1>Laporan Penjualan</h1>
<form method="get" action="<?= url('laporan') ?>" class="card filter">
  <input type="hidden" name="url" value="laporan">
  <label>Dari</label><input type="date" name="dari" value="<?= e($dari) ?>">
  <label>Sampai</label><input type="date" name="sampai" value="<?= e($sampai) ?>">
  <button class="btn small">Tampilkan</button>
</form>

<div class="card"><h3>Rekap per hari</h3>
<table><thead><tr><th>Tanggal</th><th>Jumlah Pesanan</th><th>Total Penjualan</th></tr></thead><tbody>
<?php if (!$harian): ?><tr><td colspan="3" class="muted">Tidak ada data.</td></tr><?php endif; ?>
<?php foreach ($harian as $r): ?><tr><td><?= e($r['hari']) ?></td><td><?= (int)$r['jumlah'] ?></td><td><?= e(rupiah($r['total'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div>

<div class="card"><h3>Menu terlaris</h3>
<table><thead><tr><th>Menu</th><th>Terjual</th><th>Omzet</th></tr></thead><tbody>
<?php if (!$terlaris): ?><tr><td colspan="3" class="muted">Tidak ada data.</td></tr><?php endif; ?>
<?php foreach ($terlaris as $r): ?><tr><td><?= e($r['nama_menu']) ?></td><td><?= (int)$r['qty'] ?></td><td><?= e(rupiah($r['omzet'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div>

<?php if (isset($perKasir)): ?>
<div class="card"><h3>Kinerja per pengguna <span class="badge">khusus owner</span></h3>
<table><thead><tr><th>Nama</th><th>Peran</th><th>Jumlah Pesanan</th><th>Total</th></tr></thead><tbody>
<?php if (!$perKasir): ?><tr><td colspan="4" class="muted">Tidak ada data.</td></tr><?php endif; ?>
<?php foreach ($perKasir as $r): ?><tr><td><?= e($r['nama']) ?></td><td><?= e($r['role']) ?></td><td><?= (int)$r['jumlah'] ?></td><td><?= e(rupiah($r['total'])) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
