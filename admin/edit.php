<?php
require_once '../config/db.php';
requireAdmin();

$id  = (int)($_GET['id'] ?? 0);
if (!$id) { flash('err', 'ID tidak valid.'); go(APP_URL . '/admin/dashboard.php'); }

// Ambil data kampanye yang akan diedit
$k = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM campaigns WHERE id = $id LIMIT 1"
));
if (!$k) { flash('err', 'Kampanye tidak ditemukan.'); go(APP_URL . '/admin/dashboard.php'); }

$err     = '';
$sukses  = '';

// ── Proses form edit ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deskripsi   = trim($_POST['deskripsi']    ?? '');
    $tgl_selesai = trim($_POST['tgl_selesai']  ?? '');

    // Validasi
    if (empty($deskripsi)) {
        $err = 'Deskripsi tidak boleh kosong.';
    } elseif (empty($tgl_selesai)) {
        $err = 'Tanggal selesai tidak boleh kosong.';
    } elseif ($tgl_selesai <= $k['tgl_mulai']) {
        $err = 'Tanggal selesai harus setelah tanggal mulai (' . $k['tgl_mulai'] . ').';
    } else {
        // Sanitasi
        $d = mysqli_real_escape_string($conn, $deskripsi);
        $t = mysqli_real_escape_string($conn, $tgl_selesai);

        // UPDATE ke database
        mysqli_query($conn,
            "UPDATE campaigns
             SET deskripsi   = '$d',
                 tgl_selesai = '$t'
             WHERE id = $id"
        );

        // Catat ke activity_logs
        log_aksi($conn, 'edit_kampanye',
            "Edit deskripsi & tanggal selesai kampanye ID $id: " . $k['judul']);

        // Refresh data kampanye dari DB agar form terisi nilai terbaru
        $k = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT * FROM campaigns WHERE id = $id LIMIT 1"
        ));

        flash('ok', 'Kampanye "' . e($k['judul']) . '" berhasil diperbarui!');
        go(APP_URL . '/admin/dashboard.php');
    }
}

$pageTitle = 'Edit Kampanye — DonasiKita';
require_once '../includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="<?= APP_URL ?>">Beranda</a> /
    <a href="<?= APP_URL ?>/admin/dashboard.php">Dashboard</a> /
    Edit Kampanye
  </div>

  <h1>EDIT KAMPANYE</h1>

  <!-- Info kampanye yang diedit -->
  <div style="background:#fff5f5;border:1px solid #fca5a5;padding:12px 16px;margin-bottom:20px;font-size:.88rem">
    <strong style="color:#cc0000">Program:</strong> <?= e($k['judul']) ?><br>
    <strong style="color:#cc0000">Kategori:</strong>
    <?php
      $kat = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT cat.nama FROM campaigns c
           LEFT JOIN categories cat ON c.category_id = cat.id
           WHERE c.id = $id LIMIT 1"
      ));
      echo e($kat['nama'] ?? '-');
    ?><br>
    <strong style="color:#cc0000">Tanggal Mulai:</strong> <?= e($k['tgl_mulai']) ?>
    &nbsp;·&nbsp;
    <strong style="color:#cc0000">Target Dana:</strong> <?= rp($k['target_dana']) ?>
  </div>

  <?php if ($err): ?>
  <div class="alert alert-err"><?= e($err) ?></div>
  <?php endif; ?>

  <form method="POST" class="form-wide">

    <!-- DESKRIPSI -->
    <label>Deskripsi Program <span style="color:#cc0000">*</span></label>
    <textarea name="deskripsi" rows="8" required
              placeholder="Ceritakan latar belakang dan tujuan program..."><?= e($k['deskripsi']) ?></textarea>
    <p style="font-size:.78rem;color:#aaa;margin-top:3px">
      Karakter: <span id="char-count">0</span>
    </p>

    <!-- TANGGAL SELESAI -->
    <label>Tanggal Selesai <span style="color:#cc0000">*</span></label>
    <input type="date" name="tgl_selesai"
           value="<?= e($k['tgl_selesai']) ?>"
           min="<?= date('Y-m-d', strtotime($k['tgl_mulai'] . ' +1 day')) ?>"
           required style="max-width:260px">
    <p style="font-size:.78rem;color:#aaa;margin-top:3px">
      Tanggal mulai: <?= e($k['tgl_mulai']) ?> — tanggal selesai harus setelahnya.
    </p>

    <!-- Tombol -->
    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
      <button type="submit">Simpan Perubahan</button>
      <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-outline">Batal</a>
      <a href="<?= APP_URL ?>/views/detail.php?id=<?= $id ?>" class="btn btn-outline">
        Lihat Kampanye
      </a>
    </div>

  </form>
</div>

<script>
  // Hitung karakter textarea deskripsi
  var ta    = document.querySelector('textarea[name="deskripsi"]');
  var count = document.getElementById('char-count');
  function updateCount() { count.textContent = ta.value.length; }
  ta.addEventListener('input', updateCount);
  updateCount(); // jalankan sekali saat halaman load
</script>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>
