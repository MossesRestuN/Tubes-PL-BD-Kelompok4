<?php $h = $header; ?>
<div class="head"><h1>Pesanan #<?= (int)$h['id_pesanan'] ?></h1><a class="btn light" href="<?= url('pesanan') ?>">Kembali</a></div>
<div class="card">
  <p><strong>Waktu:</strong> <?= e($h['tanggal']) ?> &nbsp; <strong>Kasir:</strong> <?= e($h['kasir']) ?></p>
  <p><strong>Pelanggan:</strong> <?= e($h['nama_pelanggan'] ?: 'Umum') ?> &nbsp; <strong>Mode:</strong> <?= e($h['mode_layanan']) ?> &nbsp; <strong>Bayar:</strong> <?= e($h['metode_pembayaran']) ?></p>
  <?php if ($h['catatan']): ?><p><strong>Catatan:</strong> <?= e($h['catatan']) ?></p><?php endif; ?>
  <table>
    <thead><tr><th>Menu</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i): ?>
      <tr><td><?= e($i['nama_menu']) ?></td><td><?= e(rupiah($i['harga_satuan'])) ?></td><td><?= (int)$i['jumlah'] ?></td><td><?= e(rupiah($i['subtotal'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="3">Total</th><th><?= e(rupiah($h['total'])) ?></th></tr></tfoot>
  </table>
</div>
