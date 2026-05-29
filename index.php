<?php
require_once 'config/db.php';
$pageTitle = 'DonasiKita';

// Statistik hero
$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT
        (SELECT COUNT(*) FROM campaigns WHERE status='aktif') AS kampanye,
        (SELECT COUNT(*) FROM donations  WHERE status='sukses') AS donatur,
        (SELECT COALESCE(SUM(jumlah),0) FROM donations WHERE status='sukses') AS total"
));

// 4 kampanye terbaru (JOIN campaigns + categories)
$rows = mysqli_query($conn,
    "SELECT c.id, c.judul, c.deskripsi, c.target_dana, c.terkumpul, c.tgl_selesai,
            cat.nama AS kategori
     FROM campaigns c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.status = 'aktif'
     ORDER BY c.created_at DESC LIMIT 4"
);

require_once 'includes/header.php';
?>

<div class="container">
  <h1>SELAMAT DATANG DI DONASIKITA</h1>
  <p>Platform donasi online untuk membantu sesama. Setiap rupiah Anda berarti bagi mereka yang membutuhkan.</p>

  <hr>

  <!-- Statistik -->
  <div class="stats-row">
    <div class="stat-box">
      <div class="num"><?= $stats['kampanye'] ?>+</div>
      <div class="lbl">Program Aktif</div>
    </div>
    <div class="stat-box">
      <div class="num"><?= number_format($stats['donatur']) ?>+</div>
      <div class="lbl">Total Donatur</div>
    </div>
    <div class="stat-box">
      <div class="num"><?= rp($stats['total']) ?></div>
      <div class="lbl">Dana Tersalurkan</div>
    </div>
  </div>

  <hr>

  <h2>PROGRAM DONASI TERBARU</h2>

  <?php
  // Cek apakah ada data — jika tidak, tampilkan pesan empty state
  if (mysqli_num_rows($rows) > 0):
    while ($k = mysqli_fetch_assoc($rows)):
      $p    = pct($k['terkumpul'], $k['target_dana']);
      $sisa = sisaHari($k['tgl_selesai']);
      // Hitung jumlah donatur per kampanye
      $jmlDonor = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT COUNT(*) AS n FROM donations WHERE campaign_id={$k['id']} AND status='sukses'"
      ))['n'];
  ?>
  <div class="kampanye-item">
    <h3><a href="<?= APP_URL ?>/views/detail.php?id=<?= $k['id'] ?>"><?= e($k['judul']) ?></a></h3>
    <p><?= e(substr($k['deskripsi'], 0, 130)) ?>...</p>
    <div class="prog"><div class="prog-fill" style="width:<?= $p ?>%"></div></div>
    <div class="meta">
      <span><?= rp($k['terkumpul']) ?> dari <?= rp($k['target_dana']) ?></span>
      <span><?= $p ?>%</span>
      <span><?= $jmlDonor ?> donatur</span>
      <span><?= $sisa > 0 ? $sisa.' hari lagi' : 'Segera berakhir' ?></span>
    </div>
    <a href="<?= APP_URL ?>/views/detail.php?id=<?= $k['id'] ?>" class="btn btn-sm" style="margin-top:10px">Donasi Sekarang</a>
  </div>
  <?php
    endwhile;
  else:
  // EMPTY STATE — tampil jika belum ada data kampanye
  ?>
  <p style="color:#aaa;padding:24px 0">
    Saat ini belum ada program donasi yang tersedia, silakan cek kembali nanti.
  </p>
  <?php endif; ?>

  <br>
  <a href="<?= APP_URL ?>/views/katalog.php" class="btn">Lihat Semua Program</a>
</div>

<?php
require_once 'includes/footer.php';
mysqli_close($conn);
?>
