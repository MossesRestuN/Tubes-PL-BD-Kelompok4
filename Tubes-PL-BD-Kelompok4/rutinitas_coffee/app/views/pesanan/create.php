<h1>Pesanan Baru</h1>
<form method="post" action="<?= url('pesanan/store') ?>" class="card form">
  <?= csrf_field() ?>
  <label>Mode Layanan</label>
  <select name="mode_layanan">
    <option value="">Pilih</option>
    <?php foreach (['Dine In', 'Take Away'] as $o): ?><option <?= ($old['mode_layanan'] ?? '') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
  </select>
  <?php if (isset($errors['mode_layanan'])): ?><div class="error"><?= e($errors['mode_layanan']) ?></div><?php endif; ?>

  <label>Metode Pembayaran</label>
  <select name="metode_pembayaran">
    <option value="">Pilih</option>
    <?php foreach (['Cash', 'QRIS'] as $o): ?><option <?= ($old['metode_pembayaran'] ?? '') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
  </select>
  <?php if (isset($errors['metode_pembayaran'])): ?><div class="error"><?= e($errors['metode_pembayaran']) ?></div><?php endif; ?>

  <label>Nama Pelanggan (opsional)</label>
  <input type="text" name="nama_pelanggan" value="<?= e($old['nama_pelanggan'] ?? '') ?>">
  <?php if (isset($errors['nama_pelanggan'])): ?><div class="error"><?= e($errors['nama_pelanggan']) ?></div><?php endif; ?>

  <label>Catatan (opsional)</label>
  <input type="text" name="catatan" value="<?= e($old['catatan'] ?? '') ?>">
  <?php if (isset($errors['catatan'])): ?><div class="error"><?= e($errors['catatan']) ?></div><?php endif; ?>

  <h3>Item Pesanan</h3>
  <p class="muted">Menu berstatus Kosong tidak ditampilkan.</p>
  <?php if (isset($errors['items'])): ?><div class="error"><?= e($errors['items']) ?></div><?php endif; ?>
  <div id="rows"></div>
  <button type="button" class="btn light small" onclick="addRow()">+ Tambah Item</button>
  <p>Estimasi total: <strong id="total">Rp0</strong></p>

  <div class="row-btn"><button class="btn">Simpan Pesanan</button><a class="btn light" href="<?= url('pesanan') ?>">Batal</a></div>
</form>

<template id="tpl">
  <div class="row">
    <select name="id_menu[]" onchange="hitung()">
      <option value="">Pilih menu</option>
      <?php foreach ($menus as $m): ?><option value="<?= (int)$m['id_menu'] ?>" data-harga="<?= e($m['harga']) ?>"><?= e($m['nama_menu']) ?> (<?= e(rupiah($m['harga'])) ?>)</option><?php endforeach; ?>
    </select>
    <input type="number" name="jumlah[]" min="1" value="1" oninput="hitung()">
    <button type="button" class="btn small danger" onclick="this.parentNode.remove();hitung()">Hapus</button>
  </div>
</template>
<script>
function addRow(){ document.getElementById('rows').appendChild(document.getElementById('tpl').content.cloneNode(true)); }
function hitung(){
  let t = 0;
  document.querySelectorAll('#rows .row').forEach(r => {
    const o = r.querySelector('select').selectedOptions[0];
    const q = parseInt(r.querySelector('input').value) || 0;
    if (o && o.dataset.harga) t += parseFloat(o.dataset.harga) * q;
  });
  document.getElementById('total').textContent = 'Rp' + t.toLocaleString('id-ID');
}
addRow();
</script>
