<?php
require_once '../config/db.php';
if (isLogin()) go(APP_URL);
$pageTitle = 'Login — DonasiKita';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (!$email || !$pass) {
        $err = 'Email dan password wajib diisi.';
    } else {
        $e = mysqli_real_escape_string($conn, $email);
        $u = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT * FROM users WHERE email = '$e' LIMIT 1"
        ));
        if ($u && password_verify($pass, $u['password'])) {
            // Simpan ke session
            $_SESSION['user'] = [
                'id'    => $u['id'],
                'nama'  => $u['nama'],
                'email' => $u['email'],
                'role'  => $u['role'],
            ];
            flash('ok', 'Selamat datang, ' . $u['nama'] . '!');
            go($u['role'] === 'admin' ? APP_URL . '/admin/dashboard.php' : APP_URL);
        } else {
            $err = 'Email atau password salah.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1>LOGIN</h1>

    <?php if ($err): ?>
        <div class="alert alert-err"><?= e($err) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-wrap">
        <label>Email</label>
        <input type="email" name="email" required
            placeholder="email@example.com"
            value="<?= e($_POST['email'] ?? '') ?>">
        <label>Password</label>
        <input type="password" name="password" required placeholder="••••••••">
        <button type="submit">Masuk</button>
    </form>

    <p style="font-size:.85rem;margin-top:16px;color:#555">
        Belum punya akun? <a href="register.php" style="color:#cc0000">Daftar di sini</a>
    </p>

    <hr>

</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>