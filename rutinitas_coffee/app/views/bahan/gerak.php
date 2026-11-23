<?php $ambil = $jenis === 'ambil'; ?>
<h1><?= $ambil ? 'Ambil Stok' : 'Tambah Stok' ?>: <?= e($bahan['nama_bahan']) ?></h1>
<form method="post" action="<?= url('bahan/' . $jenis . '/' . $bahan['id_bahan']) ?>" class="card form">
  <?= csrf_field() ?>
  <p>Stok saat ini: <strong><?= e($bahan['stok'] + 0) ?> <?= e($bahan['satuan']) ?></strong></p>
  <p class="muted"><?= $ambil ? 'Isi jumlah yang diambil dari gudang ke kitchen karena bahan di kitchen habis.' : 'Isi jumlah stok yang masuk. Siapa yang menambahkan akan tercatat di riwayat.' ?></p>

  <label for="jumlah">Jumlah (<?= e($bahan['satuan']) ?>)</label>
  <input id="jumlah" type="number" name="jumlah" step="any" min="0.01" value="<?= e($old['jumlah']) ?>">
  <?php if (isset($errors['jumlah'])): ?><div class="error"><?= e($errors['jumlah']) ?></div><?php endif; ?>

  <label for="keterangan">Keterangan (opsional)</label>
  <input id="keterangan" type="text" name="keterangan" maxlength="255" value="<?= e($old['keterangan']) ?>" placeholder="<?= $ambil ? 'Contoh: kitchen habis shift malam' : 'Contoh: kiriman Supplier B, nota 123' ?>">
  <?php if (isset($errors['keterangan'])): ?><div class="error"><?= e($errors['keterangan']) ?></div><?php endif; ?>

  <p class="muted">Dicatat atas nama: <strong><?= e(Auth::user()['nama']) ?></strong></p>
  <div class="row-btn">
    <button class="btn">Simpan</button>
    <a class="btn light" href="<?= url('bahan') ?>">Batal</a>
  </div>
</form>
