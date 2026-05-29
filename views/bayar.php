<?php
require_once '../config/db.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) go(APP_URL . '/views/katalog.php');

$k = mysqli_fetch_assoc(mysqli_query(
  $conn,
  "SELECT * FROM campaigns WHERE id = $id AND status = 'aktif' LIMIT 1"
));
if (!$k) {
  flash('err', 'Program tidak ditemukan.');
  go(APP_URL . '/views/katalog.php');
}

$err = '';
$u   = user();

// ── Proses form donasi ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama   = trim($_POST['nama']   ?? '') ?: 'Hamba Allah';
  $jumlah = (float)preg_replace('/[^0-9]/', '', $_POST['jumlah'] ?? 0);
  $metode = in_array($_POST['metode'], ['GoPay', 'OVO', 'DANA', 'Transfer']) ? $_POST['metode'] : 'Transfer';
  $pesan  = trim($_POST['pesan']  ?? '');

  if ($jumlah < 1000) {
    $err = 'Minimal donasi Rp 1.000.';
  } else {
    $uid   = $u ? $u['id'] : 'NULL';
    $nama  = mysqli_real_escape_string($conn, $nama);
    $pesan = mysqli_real_escape_string($conn, $pesan);

    // INSERT donasi — langsung status sukses
    mysqli_query(
      $conn,
      "INSERT INTO donations (campaign_id, user_id, nama_donatur, jumlah, pesan, metode, status)
             VALUES ($id, $uid, '$nama', $jumlah, '$pesan', '$metode', 'sukses')"
    );
    $donasi_id = mysqli_insert_id($conn);

    // UPDATE terkumpul di campaigns
    mysqli_query(
      $conn,
      "UPDATE campaigns SET terkumpul = terkumpul + $jumlah WHERE id = $id"
    );

    // Catat log
    log_aksi($conn, 'donasi', "Donasi " . rp($jumlah) . " ke campaign ID $id");

    // Redirect ke halaman sukses
    go(APP_URL . "/views/sukses.php?donasi_id=$donasi_id");
  }
}

$p         = pct($k['terkumpul'], $k['target_dana']);
$pageTitle = 'Form Donasi — DonasiKita';
require_once '../includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="<?= APP_URL ?>">Beranda</a> /
    <a href="<?= APP_URL ?>/views/katalog.php">Katalog</a> /
    <a href="<?= APP_URL ?>/views/detail.php?id=<?= $id ?>">Detail</a> /
    Form Donasi
  </div>
  <h1>FORM DONASI</h1>

  <p style="font-size:.88rem;color:#555;margin-bottom:3px">Program:</p>
  <p style="font-weight:bold;color:#cc0000;margin-bottom:10px"><?= e($k['judul']) ?></p>

  <!-- Progress saat ini -->
  <div class="prog" style="max-width:460px">
    <div class="prog-fill" style="width:<?= $p ?>%"></div>
  </div>
  <p style="font-size:.8rem;color:#888;margin:4px 0 20px">
    <?= rp($k['terkumpul']) ?> terkumpul &middot; <?= $p ?>% dari <?= rp($k['target_dana']) ?>
  </p>

  <?php if ($err): ?>
    <div class="alert alert-err"><?= e($err) ?></div>
  <?php endif; ?>

  <form method="POST" class="form-wrap">
    <label>Nama Donatur</label>
    <input type="text" name="nama"
      value="<?= e($u['nama'] ?? '') ?>"
      placeholder="Nama kamu / Hamba Allah">

    <label>Jumlah Donasi (Rp) *</label>
    <input type="number" name="jumlah" id="inp-jumlah"
      min="1000" placeholder="Contoh: 50000"
      oninput="updatePreview()" required>
    <div class="quick">
      <?php foreach ([10000, 25000, 50000, 100000, 250000, 500000] as $n): ?>
        <button type="button" onclick="document.getElementById('inp-jumlah').value=<?= $n ?>;updatePreview()">
          <?= rp($n) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <label>Metode Pembayaran *</label>
    <div class="pay-opts">
      <?php foreach (['GoPay', 'OVO', 'DANA', 'Transfer'] as $m => $ic): ?>
        <label>
          <input type="radio" name="metode" value="<?= $m ?>" <?= $m === 'GoPay' ? 'checked' : '' ?>>
          <?= $ic ?> <?= $m ?>
        </label>
      <?php endforeach; ?>
    </div>

    <label>Pesan / Doa (opsional)</label>
    <textarea name="pesan" placeholder="Semoga bermanfaat..."></textarea>

    <p style="font-size:.83rem;color:#888;margin-top:12px">
      Total donasi: <strong id="preview-jml">—</strong>
    </p>

    <button type="submit">Konfirmasi &amp; Donasi Sekarang</button>
    &nbsp;
    <a href="<?= APP_URL ?>/views/detail.php?id=<?= $id ?>" class="btn btn-outline">Batal</a>
  </form>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>