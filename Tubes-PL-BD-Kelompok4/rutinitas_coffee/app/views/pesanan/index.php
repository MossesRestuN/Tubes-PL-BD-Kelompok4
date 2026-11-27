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
        <button type="button" class="btn small light" data-detail="<?= (int)$r['id_pesanan'] ?>">Detail</button>
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

<!-- Rincian pesanan tampil mengambang, data diambil sesuai kebutuhan tanpa pindah halaman -->
<dialog id="detail" class="wide">
  <div class="dlg">
    <div class="dlg-head"><h3 id="dTitle">Pesanan</h3><button type="button" class="x" data-close aria-label="Tutup">&times;</button></div>
    <div id="dBody"><p class="muted">Memuat...</p></div>
  </div>
</dialog>
<script>
(function () {
  const dlg = document.getElementById('detail'), body = document.getElementById('dBody'), title = document.getElementById('dTitle');
  const base = <?= json_encode(url('pesanan/rincian/')) ?>;
  const el = (tag, text, cls) => { const n = document.createElement(tag); if (text !== undefined) n.textContent = text; if (cls) n.className = cls; return n; };
  const baris = (label, val) => { const p = el('p'); p.style.margin = '0 0 4px'; p.append(el('strong', label + ': '), document.createTextNode(val)); return p; };

  function tampil(d) {
    title.textContent = 'Pesanan #' + d.id;
    const t = el('table'), head = el('thead'), hr = el('tr');
    ['Menu', 'Harga', 'Jumlah', 'Subtotal'].forEach(h => hr.append(el('th', h)));
    head.append(hr); t.append(head);
    const tb = el('tbody');
    d.items.forEach(i => { const r = el('tr'); [i.menu, i.harga, i.jumlah, i.subtotal].forEach(v => r.append(el('td', String(v)))); tb.append(r); });
    t.append(tb);
    const tf = el('tfoot'), fr = el('tr'), th = el('th', 'Total'); th.colSpan = 3; fr.append(th, el('th', d.total)); tf.append(fr); t.append(tf);
    const info = el('div');
    info.append(baris('Waktu', d.tanggal), baris('Kasir', d.kasir), baris('Pelanggan', d.pelanggan), baris('Mode', d.mode), baris('Bayar', d.bayar));
    if (d.catatan) info.append(baris('Catatan', d.catatan));
    info.style.marginBottom = '12px';
    body.replaceChildren(info, t);
  }

  async function buka(id) {
    title.textContent = 'Pesanan #' + id;
    body.replaceChildren(el('p', 'Memuat...', 'muted'));
    if (!dlg.open) dlg.showModal();
    try {
      const r = await fetch(base + id, { headers: { 'Accept': 'application/json' } });
      const d = await r.json();
      if (!r.ok) throw new Error(d.error || 'Gagal memuat rincian.');
      tampil(d);
    } catch (e) { body.replaceChildren(el('p', e.message, 'error')); }
  }

  document.querySelectorAll('[data-detail]').forEach(b => b.addEventListener('click', () => buka(b.dataset.detail)));
  // Setelah pesanan baru disimpan, rinciannya langsung terbuka
  const auto = parseInt(new URLSearchParams(location.search).get('detail'), 10);
  if (auto > 0) buka(auto);
})();
</script>
