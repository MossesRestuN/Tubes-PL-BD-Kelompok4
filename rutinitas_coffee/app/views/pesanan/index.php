<div class="head"><h1>Pesanan</h1><a class="btn" href="<?= url('pesanan/create') ?>">+ Pesanan Baru</a></div>
<?php if (Auth::role() === 'karyawan'): ?><p class="muted">Menampilkan pesanan yang Anda input.</p><?php endif; ?>
<div class="card">
<table>
  <thead><tr><th>No</th><th>Waktu</th><th>Kasir</th><th>Pelanggan</th><th>Mode</th><th>Bayar</th><th>Total</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="8" class="muted">Belum ada pesanan.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td>#<?= (int)$r['id_pesanan'] ?></td><td><?= e($r['tanggal']) ?></td><td><?= e($r['kasir']) ?></td>
      <td><?= e($r['nama_pelanggan'] ?: '-') ?></td><td><?= e($r['mode_layanan']) ?></td><td><?= e($r['metode_pembayaran']) ?></td>
      <td><?= e(rupiah($r['total'])) ?></td>
      <td class="actions">
        <a class="btn small light" href="<?= url('pesanan/show/' . $r['id_pesanan']) ?>">Detail</a>
        <?php if (Auth::can(['manajer', 'owner'])): ?>
        <form method="post" action="<?= url('pesanan/destroy/' . $r['id_pesanan']) ?>" class="inline" onsubmit="return confirm('Batalkan pesanan ini?')">
          <?= csrf_field() ?><button class="btn small danger">Batalkan</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
