<div class="head">
  <div>
    <h1>Bahan Baku</h1>
    <p class="muted">Pantau stok gudang dan siapa yang mengambil atau menambah bahan.</p>
  </div>
  <?php if ($canWrite): ?><a class="btn" href="<?= url('bahan/create') ?>">+ Tambah Bahan</a><?php endif; ?>
</div>

<div class="card">
  <h3>Stok Gudang</h3>
  <table>
    <thead><tr><th>Bahan</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="6" class="muted">Belum ada data.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): $menipis = (float)$r['stok'] <= (float)$r['stok_minimum']; ?>
      <tr>
        <td><?= e($r['nama_bahan']) ?><?php if ($r['vendor']): ?><div class="muted"><?= e($r['vendor']) ?></div><?php endif; ?></td>
        <td><?= e($r['kategori']) ?></td>
        <td><?= e($r['stok'] + 0) ?> <?= e($r['satuan']) ?></td>
        <td><?= e($r['stok_minimum'] + 0) ?> <?= e($r['satuan']) ?></td>
        <td><?= $menipis ? '<span class="badge">Menipis</span>' : 'Aman' ?></td>
        <td class="actions">
          <button type="button" class="btn small" data-gerak="ambil" data-id="<?= (int)$r['id_bahan'] ?>" data-nama="<?= e($r['nama_bahan']) ?>" data-stok="<?= e($r['stok'] + 0) ?>" data-satuan="<?= e($r['satuan']) ?>">Ambil</button>
          <button type="button" class="btn small light" data-gerak="tambah" data-id="<?= (int)$r['id_bahan'] ?>" data-nama="<?= e($r['nama_bahan']) ?>" data-stok="<?= e($r['stok'] + 0) ?>" data-satuan="<?= e($r['satuan']) ?>">Tambah</button>
          <?php if ($canWrite): ?>
          <details class="more">
            <summary aria-label="Menu lain">&#8942;</summary>
            <div class="more-menu">
              <a href="<?= url('bahan/riwayat/' . $r['id_bahan']) ?>">Riwayat bahan ini</a>
              <a href="<?= url('bahan/edit/' . $r['id_bahan']) ?>">Ubah data</a>
              <form method="post" action="<?= url('bahan/destroy/' . $r['id_bahan']) ?>" onsubmit="return confirm('Hapus bahan ini beserta riwayatnya?')">
                <?= csrf_field() ?><button class="danger-link">Hapus</button>
              </form>
            </div>
          </details>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card" id="histori">
  <div class="head" style="margin-bottom:12px">
    <h3 style="margin:0">Histori Pengambilan dan Stok Masuk</h3>
    <div class="tabs">
      <a href="<?= url('bahan') ?>#histori" class="<?= $filter === 'semua' ? 'on' : '' ?>">Semua</a>
      <a href="<?= url('bahan') ?>&amp;jenis=ambil#histori" class="<?= $filter === 'ambil' ? 'on' : '' ?>">Ambil</a>
      <a href="<?= url('bahan') ?>&amp;jenis=tambah#histori" class="<?= $filter === 'tambah' ? 'on' : '' ?>">Masuk</a>
    </div>
  </div>
  <table>
    <thead><tr><th>Waktu</th><th>Petugas</th><th>Aktivitas</th><th>Bahan</th><th>Jumlah</th><th>Keterangan</th></tr></thead>
    <tbody>
    <?php if (!$riwayat): ?><tr><td colspan="6" class="muted">Belum ada histori. Catat pengambilan atau penambahan stok lewat tombol di atas.</td></tr><?php endif; ?>
    <?php foreach ($riwayat as $h):
        $label = ['awal' => 'Stok awal', 'ambil' => 'Ambil gudang', 'tambah' => 'Stok masuk'][$h['jenis']] ?? $h['jenis']; ?>
      <tr>
        <td><?= e(date('d M, H:i', strtotime($h['tanggal']))) ?></td>
        <td><?= e($h['petugas']) ?> <span class="muted">(<?= e($h['role']) ?>)</span></td>
        <td><span class="badge"><?= e($label) ?></span></td>
        <td><?= e($h['nama_bahan']) ?></td>
        <td><?= e(($h['jenis'] === 'ambil' ? '-' : '+') . ($h['jumlah'] + 0)) ?> <?= e($h['satuan']) ?></td>
        <td><?= e($h['keterangan'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p style="margin:12px 0 0"><a href="<?= url('bahan/riwayat') ?>">Lihat semua histori</a></p>
</div>

<dialog id="gerak">
  <form method="post" id="gerakForm" class="form">
    <?= csrf_field() ?>
    <h3 id="gTitle"></h3>
    <p class="muted" id="gInfo"></p>
    <label for="gJumlah" id="gLabel">Jumlah</label>
    <input id="gJumlah" type="number" name="jumlah" step="any" min="0.01" value="1" required>
    <label for="gKet">Keterangan (opsional)</label>
    <input id="gKet" type="text" name="keterangan" maxlength="255">
    <p class="muted">Dicatat atas nama: <strong><?= e(Auth::user()['nama']) ?></strong></p>
    <div class="row-btn">
      <button class="btn">Simpan</button>
      <button type="button" class="btn light" onclick="document.getElementById('gerak').close()">Batal</button>
    </div>
  </form>
</dialog>
<script>
(function () {
  const dlg = document.getElementById('gerak'), form = document.getElementById('gerakForm');
  const base = <?= json_encode(url('bahan/')) ?>;
  document.querySelectorAll('[data-gerak]').forEach(b => b.addEventListener('click', () => {
    const d = b.dataset, ambil = d.gerak === 'ambil';
    form.action = base + d.gerak + '/' + d.id;
    document.getElementById('gTitle').textContent = (ambil ? 'Ambil stok: ' : 'Tambah stok: ') + d.nama;
    document.getElementById('gInfo').textContent = 'Stok gudang saat ini: ' + d.stok + ' ' + d.satuan;
    document.getElementById('gLabel').textContent = 'Jumlah (' + d.satuan + ')';
    document.getElementById('gJumlah').value = 1;
    document.getElementById('gKet').value = '';
    document.getElementById('gKet').placeholder = ambil ? 'Contoh: kitchen habis shift malam' : 'Contoh: kiriman Supplier B';
    if (ambil) document.getElementById('gJumlah').max = d.stok; else document.getElementById('gJumlah').removeAttribute('max');
    dlg.showModal();
  }));
  dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
  document.addEventListener('click', e => {
    document.querySelectorAll('details.more[open]').forEach(x => { if (!x.contains(e.target)) x.removeAttribute('open'); });
  });
})();
</script>
