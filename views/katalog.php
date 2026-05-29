<?php
require_once '../config/db.php';
$pageTitle = 'Katalog Donasi — DonasiKita';

// Ambil filter dari GET
$q   = mysqli_real_escape_string($conn, trim($_GET['q']   ?? ''));
$kat = mysqli_real_escape_string($conn, trim($_GET['kat'] ?? ''));

// Query JOIN: campaigns + categories + hitung donatur
$where = "WHERE c.status = 'aktif'";
if ($kat) $where .= " AND cat.slug = '$kat'";
if ($q)   $where .= " AND c.judul LIKE '%$q%'";

$rows = mysqli_query($conn,
    "SELECT c.id, c.judul, c.deskripsi, c.target_dana, c.terkumpul, c.tgl_selesai,
            cat.nama AS kategori, cat.slug AS kat_slug,
            COUNT(d.id) AS jml_donor
     FROM campaigns c
     LEFT JOIN categories cat ON c.category_id = cat.id
     LEFT JOIN donations   d  ON c.id = d.campaign_id AND d.status = 'sukses'
     $where
     GROUP BY c.id
     ORDER BY c.created_at DESC"
);
$total = mysqli_num_rows($rows);

// Ambil semua kategori untuk dropdown
$cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY nama");

require_once '../includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="<?= APP_URL ?>">Beranda</a> / Katalog Donasi
  </div>
  <h1>KATALOG PROGRAM DONASI</h1>

  <!-- Filter & Pencarian -->
  <form method="GET" action="katalog.php">
    <div class="filter-row">
      <input type="text" name="q" placeholder="Cari program..." value="<?= e($q) ?>">
      <select name="kat">
        <option value="">Semua Kategori</option>
        <?php while ($c = mysqli_fetch_assoc($cats)): ?>
        <option value="<?= e($c['slug']) ?>" <?= $kat === $c['slug'] ? 'selected' : '' ?>>
          <?= e($c['nama']) ?>
        </option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Cari</button>
      <?php if ($q || $kat): ?>
      <a href="katalog.php" class="btn btn-outline">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <p style="font-size:.83rem;color:#888;margin-bottom:12px">
    Menampilkan <strong><?= $total ?></strong> program
    <?= $q ? "untuk \"<em>".e($q)."</em>\"" : '' ?>
    <?= $kat ? "kategori <em>".e(ucwords(str_replace('-',' ',$kat)))."</em>" : '' ?>
  </p>

  <!-- Daftar Kampanye -->
  <?php
  if ($total > 0):
    while ($k = mysqli_fetch_assoc($rows)):
      $p    = pct($k['terkumpul'], $k['target_dana']);
      $sisa = sisaHari($k['tgl_selesai']);
  ?>
  <div class="kampanye-item">
    <h3><a href="<?= APP_URL ?>/views/detail.php?id=<?= $k['id'] ?>"><?= e($k['judul']) ?></a></h3>
    <p><?= e(substr($k['deskripsi'], 0, 130)) ?>...</p>
    <div class="prog"><div class="prog-fill" style="width:<?= $p ?>%"></div></div>
    <div class="meta">
      <span><?= rp($k['terkumpul']) ?> dari <?= rp($k['target_dana']) ?></span>
      <span><?= $p ?>%</span>
      <span><?= $k['jml_donor'] ?> donatur</span>
      <span><?= $sisa > 0 ? $sisa.' hari lagi' : 'Segera berakhir' ?></span>
    </div>
    <a href="<?= APP_URL ?>/views/detail.php?id=<?= $k['id'] ?>" class="btn btn-sm" style="margin-top:10px">Donasi Sekarang</a>
  </div>
  <?php
    endwhile;
  else:
  ?>
  <p style="color:#aaa;padding:24px 0">
    <?= ($q || $kat) ? 'Tidak ada program yang sesuai dengan pencarian.' : 'Saat ini belum ada program donasi yang tersedia, silakan cek kembali nanti.' ?>
  </p>
  <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>
