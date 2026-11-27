<div class="login card">
  <div class="login-head"><h1><img class="brand-logo" src="<?= asset('logo-dark.png') ?>" alt="Rutinitas Coffee"></h1><p>Sistem Informasi Operasional Internal</p></div>
  <form method="post" action="<?= url('auth/login') ?>" class="form">
    <?= csrf_field() ?>
    <label>Username</label>
    <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autofocus autocomplete="username">
    <label>Password</label>
    <input type="password" name="password" autocomplete="current-password">
    <button class="btn block">Masuk</button>
  </form>
</div>