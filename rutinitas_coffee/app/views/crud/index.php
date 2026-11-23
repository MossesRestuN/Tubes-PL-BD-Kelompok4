<div class="head">
  <h1><?= e($title) ?></h1>
  <?php if ($canWrite): ?><a class="btn" href="<?= url($slug . '/create') ?>">+ Tambah</a><?php endif; ?>
</div>
<div class="card">
<table>
  <thead><tr>
    <?php foreach ($fields as $f): if (($f['type'] ?? '') === 'password') continue; ?><th><?= e($f['label']) ?></th><?php endforeach; ?>
    <?php if ($canWrite): ?><th>Aksi</th><?php endif; ?>
  </tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="9" class="muted">Belum ada data.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <?php foreach ($fields as $n => $f): if (($f['type'] ?? '') === 'password') continue; ?>
        <td><?= ($f['format'] ?? '') === 'rupiah' ? e(rupiah($r[$n])) : e(($f['type'] ?? '') === 'number' ? $r[$n] + 0 : $r[$n]) ?></td>
      <?php endforeach; ?>
      <?php if ($canWrite): ?>
      <td class="actions">
        <a class="btn small light" href="<?= url($slug . '/edit/' . $r[$pk]) ?>">Ubah</a>
        <form method="post" action="<?= url($slug . '/destroy/' . $r[$pk]) ?>" class="inline" onsubmit="return confirm('Hapus data ini?')">
          <?= csrf_field() ?><button class="btn small danger">Hapus</button>
        </form>
      </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
