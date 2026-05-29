<?php
require_once '../config/db.php';
if (isLogin()) go(APP_URL);
$pageTitle = 'Daftar — DonasiKita';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama']     ?? '');
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $conf  = trim($_POST['confirm']  ?? '');

    if (!$nama || !$email || !$pass || !$conf)
        $err = 'Semua field wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $err = 'Format email tidak valid.';
    elseif (strlen($pass) < 6)
        $err = 'Password minimal 6 karakter.';
    elseif ($pass !== $conf)
        $err = 'Konfirmasi password tidak cocok.';
    else {
        $e = mysqli_real_escape_string($conn, $email);
        if (mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$e' LIMIT 1"))) {
            $err = 'Email sudah terdaftar.';
        } else {
            $n    = mysqli_real_escape_string($conn, $nama);
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            mysqli_query($conn, "INSERT INTO users(nama,email,password,role) VALUES('$n','$e','$hash','donatur')");
            $uid = mysqli_insert_id($conn);
            $_SESSION['user'] = ['id'=>$uid,'nama'=>$nama,'email'=>$email,'role'=>'donatur'];
            flash('ok', 'Akun berhasil dibuat! Selamat datang, ' . $nama . '!');
            go(APP_URL);
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
  <h1>DAFTAR AKUN</h1>

  <?php if ($err): ?>
  <div class="alert alert-err"><?= e($err) ?></div>
  <?php endif; ?>

  <form method="POST" class="form-wrap">
    <label>Nama Lengkap *</label>
    <input type="text" name="nama" required placeholder="Nama kamu"
           value="<?= e($_POST['nama'] ?? '') ?>">
    <label>Email *</label>
    <input type="email" name="email" required placeholder="email@example.com"
           value="<?= e($_POST['email'] ?? '') ?>">
    <label>Password * (min. 6 karakter)</label>
    <input type="password" name="password" required placeholder="••••••••">
    <label>Konfirmasi Password *</label>
    <input type="password" name="confirm" required placeholder="Ulangi password">
    <button type="submit">Daftar Sekarang</button>
  </form>

  <p style="font-size:.85rem;margin-top:16px;color:#555">
    Sudah punya akun? <a href="login.php" style="color:#cc0000">Login di sini</a>
  </p>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>
