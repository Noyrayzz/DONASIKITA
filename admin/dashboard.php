<?php
require_once '../config/db.php';
requireAdmin();
$pageTitle = 'Dashboard — DonasiKita';

// Statistik
$stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT
        (SELECT COUNT(*) FROM campaigns)                          AS kampanye,
        (SELECT COUNT(*) FROM campaigns WHERE status='aktif')     AS aktif,
        (SELECT COUNT(*) FROM donations  WHERE status='sukses')   AS donasi,
        (SELECT COUNT(*) FROM users WHERE role='donatur')         AS donatur,
        (SELECT COALESCE(SUM(jumlah),0) FROM donations WHERE status='sukses') AS total"
));

// Semua kampanye (JOIN categories + hitung donasi)
$rows = mysqli_query($conn,
    "SELECT c.id, c.judul, c.target_dana, c.terkumpul, c.status,
            cat.nama AS kategori,
            COUNT(d.id) AS jml_donasi
     FROM campaigns c
     LEFT JOIN categories cat ON c.category_id = cat.id
     LEFT JOIN donations   d  ON c.id = d.campaign_id AND d.status = 'sukses'
     GROUP BY c.id
     ORDER BY c.created_at DESC"
);

// Donasi terbaru (JOIN campaigns)
$recent = mysqli_query($conn,
    "SELECT d.nama_donatur, d.jumlah, d.metode, d.created_at, c.judul AS kampanye
     FROM donations d
     LEFT JOIN campaigns c ON d.campaign_id = c.id
     WHERE d.status = 'sukses'
     ORDER BY d.created_at DESC LIMIT 6"
);

require_once '../includes/header.php';
?>

<div class="container">
  <h1>DASHBOARD ADMIN</h1>
  <p style="color:#555;margin-bottom:16px">Selamat datang, <strong><?= e(user()['nama']) ?></strong></p>
  <a href="tambah.php" class="btn">+ Tambah Program Baru</a>

  <hr>

  <!-- Statistik -->
  <h2>STATISTIK</h2>
  <div class="stats-row">
    <div class="stat-box"><div class="num"><?= $stats['kampanye'] ?></div><div class="lbl">Total Kampanye</div></div>
    <div class="stat-box"><div class="num"><?= $stats['aktif'] ?></div><div class="lbl">Kampanye Aktif</div></div>
    <div class="stat-box"><div class="num"><?= $stats['donasi'] ?></div><div class="lbl">Total Donasi</div></div>
    <div class="stat-box"><div class="num"><?= $stats['donatur'] ?></div><div class="lbl">Donatur</div></div>
  </div>
  <p style="font-size:.88rem;color:#555">
    Total dana terkumpul: <strong style="color:#cc0000"><?= rp($stats['total']) ?></strong>
  </p>

  <hr>

  <!-- Tabel Kampanye -->
  <h2>DAFTAR KAMPANYE</h2>
  <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th>Judul</th>
          <th>Kategori</th>
          <th>Terkumpul</th>
          <th>Progress</th>
          <th>Donatur</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($rows) > 0):
          while ($r = mysqli_fetch_assoc($rows)):
            $p = pct($r['terkumpul'], $r['target_dana']); ?>
        <tr>
          <td><?= e(substr($r['judul'],0,45)) ?><?= strlen($r['judul'])>45?'…':'' ?></td>
          <td><?= e($r['kategori']) ?></td>
          <td><?= rp($r['terkumpul']) ?></td>
          <td>
            <div class="prog" style="width:100px;display:inline-block">
              <div class="prog-fill" style="width:<?= $p ?>%"></div>
            </div>
            <small style="color:#cc0000"> <?= $p ?>%</small>
          </td>
          <td><?= $r['jml_donasi'] ?></td>
          <td><?= e($r['status']) ?></td>
          <td style="white-space:nowrap">
            <a href="<?= APP_URL ?>/views/detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline">Lihat</a>
            <a href="<?= APP_URL ?>/admin/edit.php?id=<?= $r['id'] ?>" class="btn btn-sm" style="background:#cc0000;color:#fff;margin-left:4px">Edit</a>
          </td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:20px">Belum ada kampanye.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <hr>

  <!-- Donasi Terbaru -->
  <h2>DONASI TERBARU</h2>
  <?php if (mysqli_num_rows($recent) > 0):
    while ($d = mysqli_fetch_assoc($recent)): ?>
  <div class="donor-item">
    <span class="name"><?= e($d['nama_donatur']) ?></span>
    <span class="amount"><?= rp($d['jumlah']) ?></span>
    <div class="msg"><?= e(substr($d['kampanye'],0,50)) ?> &middot; <?= e($d['metode']) ?></div>
    <div class="time"><?= date('d M Y, H:i', strtotime($d['created_at'])) ?></div>
  </div>
  <?php endwhile;
  else: ?>
  <p style="color:#aaa">Belum ada donasi.</p>
  <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>
