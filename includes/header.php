<?php
// Ambil flash message
$flash = getFlash();
$u     = user();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?? 'DonasiKita' ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>

<body>

  <nav>
    <a href="<?= APP_URL ?>" class="nav-logo">DONASIKITA</a>
    <ul class="nav-links">
      <li><a href="<?= APP_URL ?>">BERANDA</a></li>
      <li><a href="<?= APP_URL ?>/views/katalog.php">DONASI</a></li>
      <?php if (isLogin()): ?>
        <li><a href="<?= APP_URL ?>/views/profile.php">PROFIL</a></li>
      <?php endif; ?>
      <?php if (isAdmin()): ?>
        <li><a href="<?= APP_URL ?>/admin/dashboard.php">DASHBOARD</a></li>
        <li><a href="<?= APP_URL ?>/admin/tambah.php">+ PROGRAM</a></li>
      <?php endif; ?>
      <?php if (isLogin()): ?>
        <li><a href="<?= APP_URL ?>/auth/logout.php">KELUAR (<?= e($u['nama']) ?>)</a></li>
      <?php else: ?>
        <li><a href="<?= APP_URL ?>/auth/register.php">DAFTAR</a></li>
        <li><a href="<?= APP_URL ?>/auth/login.php">LOGIN</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <?php if ($flash): ?>
    <div class="container" style="padding-top:16px;padding-bottom:0">
      <div class="alert alert-<?= $flash['t'] === 'ok' ? 'ok' : 'err' ?>">
        <?= e($flash['m']) ?>
      </div>
    </div>
  <?php endif; ?>