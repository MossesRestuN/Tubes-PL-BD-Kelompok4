<?php $isEdit = $id !== null; ?>
<h1><?= $isEdit ? 'Ubah' : 'Tambah' ?> <?= e($title) ?></h1>
<form method="post" action="<?= url($slug . ($isEdit ? '/update/' . $id : '/store')) ?>" class="card form">
  <?= csrf_field() ?>
  <?php foreach ($fields as $n => $f): $type = $f['type'] ?? 'text'; $val = $type === 'password' ? '' : ($row[$n] ?? ''); ?>
    <label for="<?= e($n) ?>"><?= e($f['label']) ?></label>
    <?php if ($type === 'select'): ?>
      <select id="<?= e($n) ?>" name="<?= e($n) ?>">
        <option value="">Pilih</option>
        <?php foreach ($f['options'] as $o): ?><option value="<?= e($o) ?>" <?= (string)$val === (string)$o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
      </select>
    <?php else: ?>
      <input id="<?= e($n) ?>" type="<?= e($type) ?>" name="<?= e($n) ?>" value="<?= e($type === 'number' && $val !== '' ? $val + 0 : $val) ?>" <?= $type === 'number' ? 'step="any"' : '' ?>>
    <?php endif; ?>
    <?php if (!empty($f['help'])): ?><small class="muted"><?= e($f['help']) ?></small><?php endif; ?>
    <?php if (isset($errors[$n])): ?><div class="error"><?= e($errors[$n]) ?></div><?php endif; ?>
  <?php endforeach; ?>
  <div class="row-btn">
    <button class="btn">Simpan</button>
    <a class="btn light" href="<?= url($slug) ?>">Batal</a>
  </div>
</form>
