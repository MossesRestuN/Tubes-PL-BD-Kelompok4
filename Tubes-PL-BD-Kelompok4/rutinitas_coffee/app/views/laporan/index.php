<?php
// Tiap tabel menampilkan $batas baris teratas; sisanya lewat tombol "Lihat semua" (jendela mengambang)
$batas = 3;
$tabel = [
    'harian'   => ['Rekap per hari', '', ['Tanggal', 'Jumlah Pesanan', 'Total Penjualan'],
        array_map(fn($r) => [$r['hari'], (int)$r['jumlah'], rupiah($r['total'])], $harian)],
    'terlaris' => ['Menu terlaris', '', ['Menu', 'Terjual', 'Omzet'],
        array_map(fn($r) => [$r['nama_menu'], (int)$r['qty'], rupiah($r['omzet'])], $terlaris)],
];
if (isset($perKasir)) {
    $tabel['kasir'] = ['Kinerja per pengguna', '<span class="badge">khusus owner</span>', ['Nama', 'Peran', 'Jumlah Pesanan', 'Total'],
        array_map(fn($r) => [$r['nama'], $r['role'], (int)$r['jumlah'], rupiah($r['total'])], $perKasir)];
}
$cetak = function (array $head, array $rows): void {
    echo '<table><thead><tr>';
    foreach ($head as $h) echo '<th>' . e($h) . '</th>';
    echo '</tr></thead><tbody>';
    if (!$rows) echo '<tr><td colspan="' . count($head) . '" class="muted">Tidak ada data.</td></tr>';
    foreach ($rows as $r) { echo '<tr>'; foreach ($r as $c) echo '<td>' . e($c) . '</td>'; echo '</tr>'; }
    echo '</tbody></table>';
};
?>
<h1>Laporan Penjualan</h1>
<form method="get" action="<?= url('laporan') ?>" class="card filter">
  <input type="hidden" name="url" value="laporan">
  <label>Dari</label><input type="date" name="dari" value="<?= e($dari) ?>">
  <label>Sampai</label><input type="date" name="sampai" value="<?= e($sampai) ?>">
  <button class="btn small">Tampilkan</button>
</form>

<?php foreach ($tabel as $key => [$judul, $extra, $head, $rows]): ?>
<div class="card">
  <h3><?= e($judul) ?> <?= $extra ?></h3>
  <?php $cetak($head, array_slice($rows, 0, $batas)); ?>
  <?php if (count($rows) > $batas): ?>
    <p style="margin:12px 0 0"><button type="button" class="link-action" data-open="lap-<?= e($key) ?>">Lihat semua <?= e(mb_strtolower($judul)) ?></button></p>
  <?php endif; ?>
</div>
<?php if (count($rows) > $batas): ?>
<dialog id="lap-<?= e($key) ?>" class="wide">
  <div class="dlg">
    <div class="dlg-head"><h3><?= e($judul) ?> <span class="muted"><?= e($dari) ?> s/d <?= e($sampai) ?></span></h3><button type="button" class="x" data-close aria-label="Tutup">&times;</button></div>
    <div class="dlg-scroll"><?php $cetak($head, $rows); ?></div>
  </div>
</dialog>
<?php endif; ?>
<?php endforeach; ?>
