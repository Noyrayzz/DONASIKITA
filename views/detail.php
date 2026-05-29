<?php
require_once '../config/db.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) go(APP_URL . '/views/katalog.php');


if (isset($_POST['quick_donate_amount'])) {
  $campaign_id = (int)$_POST['campaign_id'] ?? 0;
  $jumlah = (float)$_POST['amount'] ?? 0;

  $result = processQuickDonate($conn, $campaign_id, $jumlah);

  if ($result['success']) {
    flash('ok', $result['message']);
    go(APP_URL . '/views/detail.php?id=' . $campaign_id);
  } else {
    flash('err', 'Gagal memproses donasi: ' . $result['message']);
    go(APP_URL . '/views/detail.php?id=' . $campaign_id);
  }
}


// Query JOIN: kampanye + kategori + pembuat
$k = mysqli_fetch_assoc(mysqli_query(
  $conn,
  "SELECT c.*, cat.nama AS kategori, u.nama AS pembuat
     FROM campaigns c
     LEFT JOIN categories cat ON c.category_id = cat.id
     LEFT JOIN users u        ON c.user_id = u.id
     WHERE c.id = $id LIMIT 1"
));
if (!$k) {
  flash('err', 'Program tidak ditemukan.');
  go(APP_URL . '/views/katalog.php');
}

$p    = pct($k['terkumpul'], $k['target_dana']);
$sisa = sisaHari($k['tgl_selesai']);

// Donatur terbaru
$donors = mysqli_query(
  $conn,
  "SELECT nama_donatur, jumlah, pesan, metode, created_at
     FROM donations
     WHERE campaign_id = $id AND status = 'sukses'
     ORDER BY created_at DESC LIMIT 10"
);
$jmlDonor = mysqli_num_rows($donors);

$pageTitle = e($k['judul']) . ' — DonasiKita';
require_once '../includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="<?= APP_URL ?>">Beranda</a> /
    <a href="<?= APP_URL ?>/views/katalog.php">Katalog</a> /
    <?= e(substr($k['judul'], 0, 40)) ?>...
  </div>

  <img src="<?= APP_URL ?>/assets/uploads/<?= e($k['gambar']) ?>"
    onerror="this.src='https://placehold.co/900x260/cc0000/fff?text=<?= urlencode($k['judul']) ?>'"
    alt="<?= e($k['judul']) ?>" class="detail-img">

  <p style="font-size:.8rem;color:#cc0000;margin-bottom:5px"><?= e($k['kategori']) ?></p>
  <h1 style="font-size:1.3rem;font-weight:bold;color:#222;margin-bottom:12px"><?= e($k['judul']) ?></h1>
  <p style="color:#555;margin-bottom:18px"><?= nl2br(e($k['deskripsi'])) ?></p>

  <hr>

  <!-- Info & Tombol Donasi -->
  <div class="info-box">
    <div class="amount"><?= rp($k['terkumpul']) ?></div>
    <p class="info">terkumpul dari target <strong><?= rp($k['target_dana']) ?></strong></p>
    <div class="prog">
      <div class="prog-fill" style="width:<?= $p ?>%"></div>
    </div>
    <p class="info">
      <strong><?= $p ?>%</strong> tercapai &nbsp;&middot;&nbsp;
      <?= $jmlDonor ?> donatur &nbsp;&middot;&nbsp;
      <?= $sisa > 0 ? $sisa . ' hari lagi' : 'Segera berakhir' ?>
    </p>
    <p class="info"><?= e($k['tgl_mulai']) ?> s/d <?= e($k['tgl_selesai']) ?></p>
    <p class="info">Oleh: <?= e($k['pembuat'] ?? 'Admin') ?></p>

    <!-- Tombol Donasi -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;">
      <a href="<?= APP_URL ?>/views/bayar.php?id=<?= $k['id'] ?>" class="btn">Donasi Sekarang</a>

      <!-- QUICK DONATE BUTTONS (Jika user login) -->
      <?php if (isLogin()): ?>
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
          <?php
          $amounts = [1000, 5000, 10000, 50000, 100000, 500000, 1000000];
          foreach ($amounts as $amt):
          ?>
            <form method="POST" action="" style="display:inline;">
              <input type="hidden" name="quick_donate_amount" value="1">
              <input type="hidden" name="campaign_id" value="<?= $id ?>">
              <input type="hidden" name="amount" value="<?= $amt ?>">
              <button type="submit" class="btn" style="background:#27ae60; border-color:#27ae60; color:#fff; cursor:pointer; padding:8px 12px; font-size:0.9rem;">
                ⚡ <?= rp($amt) ?>
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <a href="<?= APP_URL ?>/auth/login.php" class="btn" style="background:#27ae60; border-color:#27ae60; color:#fff;">
          ⚡ Quick Donate (Login dulu)
        </a>
      <?php endif; ?>

      <a href="<?= APP_URL ?>/views/katalog.php" class="btn btn-outline">Kembali</a>
    </div>
  </div>

  <hr>

  <!-- Donatur -->
  <h2>DONATUR (<?= $jmlDonor ?>)</h2>
  <?php if ($jmlDonor > 0): ?>
    <?php while ($d = mysqli_fetch_assoc($donors)): ?>
      <div class="donor-item">
        <span class="name"><?= e($d['nama_donatur']) ?></span>
        <span class="amount"><?= rp($d['jumlah']) ?></span>
        <?php if ($d['pesan']): ?>
          <div class="msg">"<?= e($d['pesan']) ?>"</div>
        <?php endif; ?>
        <div class="time">
          <?= e($d['metode']) ?> &middot;
          <?= date('d M Y, H:i', strtotime($d['created_at'])) ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="color:#aaa">Jadilah donatur pertama untuk program ini!</p>
  <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>