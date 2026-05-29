<?php
require_once '../config/db.php';
requireAdmin();
$pageTitle = 'Tambah Program — DonasiKita';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = trim($_POST['judul']        ?? '');
    $cat    = (int)($_POST['category_id'] ?? 0);
    $desc   = trim($_POST['deskripsi']    ?? '');
    $target = (float)preg_replace('/[^0-9]/', '', $_POST['target_dana'] ?? 0);
    $mulai  = $_POST['tgl_mulai']         ?? '';
    $selesai= $_POST['tgl_selesai']       ?? '';
    $uid    = user()['id'];

    if (!$judul || !$cat || !$desc || $target < 10000 || !$mulai || !$selesai)
        $err = 'Semua field wajib diisi. Target dana minimal Rp 10.000.';
    elseif ($selesai <= $mulai)
        $err = 'Tanggal selesai harus setelah tanggal mulai.';
    else {
        // Upload gambar (opsional)
        $gambar = 'default.jpg';
        if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['gambar']['size'] <= 2097152) {
                $gambar = 'kamp_' . time() . '.' . $ext;
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
                move_uploaded_file($_FILES['gambar']['tmp_name'], UPLOAD_PATH . $gambar);
            } else {
                $err = 'Format gambar harus JPG/PNG/WebP, maks 2MB.';
            }
        }

        if (!$err) {
            $j = mysqli_real_escape_string($conn, $judul);
            $d = mysqli_real_escape_string($conn, $desc);
            $s = preg_replace('/[^a-z0-9-]+/', '-', strtolower($j)) . '-' . time();

            mysqli_query($conn,
                "INSERT INTO campaigns
                    (category_id, user_id, judul, slug, deskripsi, gambar, target_dana, tgl_mulai, tgl_selesai)
                 VALUES
                    ($cat, $uid, '$j', '$s', '$d', '$gambar', $target, '$mulai', '$selesai')"
            );
            log_aksi($conn, 'tambah_kampanye', "Tambah: $judul");
            flash('ok', 'Program "' . $judul . '" berhasil ditambahkan!');
            go(APP_URL . '/admin/dashboard.php');
        }
    }
}

$cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY nama");
require_once '../includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="<?= APP_URL ?>">Beranda</a> /
    <a href="<?= APP_URL ?>/admin/dashboard.php">Dashboard</a> /
    Tambah Program
  </div>
  <h1>TAMBAH PROGRAM DONASI</h1>

  <?php if ($err): ?>
  <div class="alert alert-err"><?= e($err) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="form-wide">
    <label>Judul Program *</label>
    <input type="text" name="judul" required maxlength="200"
           placeholder="Contoh: Bantu Biaya Sekolah Anak Yatim"
           value="<?= e($_POST['judul'] ?? '') ?>">

    <div class="form-row">
      <div>
        <label>Kategori *</label>
        <select name="category_id" required>
          <option value="">— Pilih Kategori —</option>
          <?php while ($c = mysqli_fetch_assoc($cats)): ?>
          <option value="<?= $c['id'] ?>"
            <?= ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
            <?= e($c['nama']) ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label>Target Dana (Rp) * — minimal Rp 10.000</label>
        <input type="number" name="target_dana" required min="10000"
               placeholder="Contoh: 5000000"
               value="<?= e($_POST['target_dana'] ?? '') ?>">
      </div>
    </div>

    <label>Deskripsi Program *</label>
    <textarea name="deskripsi" rows="6" required
              placeholder="Ceritakan latar belakang dan tujuan program..."><?= e($_POST['deskripsi'] ?? '') ?></textarea>

    <div class="form-row">
      <div>
        <label>Tanggal Mulai *</label>
        <input type="date" name="tgl_mulai" required
               value="<?= e($_POST['tgl_mulai'] ?? date('Y-m-d')) ?>">
      </div>
      <div>
        <label>Tanggal Selesai *</label>
        <input type="date" name="tgl_selesai" required
               value="<?= e($_POST['tgl_selesai'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
      </div>
    </div>

    <label>Foto Program (opsional — JPG/PNG/WebP, maks 2MB)</label>
    <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp"
           style="padding:4px 0;border:none">

    <div style="display:flex;gap:10px;margin-top:16px">
      <button type="submit">Simpan Program</button>
      <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>
