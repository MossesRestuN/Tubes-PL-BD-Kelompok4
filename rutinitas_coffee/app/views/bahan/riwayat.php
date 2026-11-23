<div class="head">
  <h1>Riwayat Stok<?= $bahan ? ': ' . e($bahan['nama_bahan']) : '' ?></h1>
  <a class="btn light" href="<?= url('bahan') ?>">Kembali</a>
</div>
<?php if ($bahan): ?><p><a href="<?= url('bahan/riwayat') ?>">Lihat riwayat semua bahan</a></p><?php endif; ?>
<div class="card">
<table>
  <thead><tr><th>Waktu</th><?php if (!$bahan): ?><th>Bahan</th><?php endif; ?><th>Jenis</th><th>Jumlah</th><th>Stok</th><th>Petugas</th><th>Keterangan</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="muted">Belum ada riwayat.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r):
      $label = ['awal' => 'Stok awal', 'ambil' => 'Diambil', 'tambah' => 'Ditambah'][$r['jenis']] ?? $r['jenis'];
      $tanda = $r['jenis'] === 'ambil' ? '-' : '+'; ?>
    <tr>
      <td><?= e($r['tanggal']) ?></td>
      <?php if (!$bahan): ?><td><?= e($r['nama_bahan']) ?></td><?php endif; ?>
      <td><span class="badge"><?= e($label) ?></span></td>
      <td><?= e($tanda . ($r['jumlah'] + 0)) ?> <?= e($r['satuan']) ?></td>
      <td><?= e($r['stok_sebelum'] + 0) ?> &rarr; <?= e($r['stok_sesudah'] + 0) ?></td>
      <td><?= e($r['petugas']) ?> <span class="muted">(<?= e($r['role']) ?>)</span></td>
      <td><?= e($r['keterangan'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
