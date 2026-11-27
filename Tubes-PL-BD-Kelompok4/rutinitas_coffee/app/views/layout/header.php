<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rutinitas Coffee</title>
<link rel="icon" type="image/png" href="<?= asset('logo.png') ?>">
<link rel="stylesheet" href="<?= asset('style.css') ?>">
</head>
<body class="<?= Auth::check() ? 'with-app' : 'guest' ?>">
<?php if (Auth::check()):
    $u = Auth::user();
    $cur = explode('/', $_GET['url'] ?? 'dashboard')[0] ?: 'dashboard';
    $semua = ['karyawan', 'manajer', 'owner'];
    $nav = [
        ['dashboard', 'Dashboard',  'gauge', $semua],
        ['pesanan',   'Pesanan',    'list',  $semua],
        ['menu',      'Menu',       'cup',   $semua],
        ['bahan',     'Bahan Baku', 'box',   $semua],
        ['laporan',   'Laporan',    'book',  ['manajer', 'owner']],
        ['pengguna',  'Pengguna',   'user',  ['owner']],
    ];
?>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><img class="brand-logo" src="<?= asset('logo-dark.png') ?>" alt="Rutinitas Coffee"></div>
    <div class="profile">
      <div class="avatar"><?= e(strtoupper(mb_substr($u['nama'], 0, 1))) ?></div>
      <div><strong><?= e($u['nama']) ?></strong><span><?= e(ucfirst($u['role'])) ?></span></div>
    </div>
    <div class="section">MENU</div>
    <nav>
      <?php foreach ($nav as [$navSlug, $navLabel, $navIcon, $navRoles]): if (!in_array($u['role'], $navRoles, true)) continue; ?>
        <a href="<?= url($navSlug) ?>" class="navlink <?= $cur === $navSlug ? 'active' : '' ?>"><?= icon($navIcon) ?> <?= e($navLabel) ?></a>
      <?php endforeach; ?>
      <form method="post" action="<?= url('auth/logout') ?>">
        <?= csrf_field() ?>
        <button class="navlink"><?= icon('logout') ?> Log Out</button>
      </form>
    </nav>
  </aside>
  <div class="content">
    <div class="topbar">
      <button type="button" class="burger" onclick="document.body.classList.toggle('nav-open')" aria-label="Menu">☰</button>
      <span class="who"><?= e($u['nama']) ?> <span class="badge"><?= e($u['role']) ?></span></span>
    </div>
    <main class="container">
<?php else: ?>
<main class="guest-wrap">
<?php endif; ?>
<?php if ($m = get_flash('success')): ?><div class="alert ok"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = get_flash('error')): ?><div class="alert err"><?= e($m) ?></div><?php endif; ?>