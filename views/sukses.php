<?php
require_once '../config/db.php';
$donasi_id = (int)($_GET['donasi_id'] ?? 0);
if (!$donasi_id) go(APP_URL . '/views/katalog.php');

// JOIN: donasi + kampanye + kategori
$d = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT d.*, c.judul AS kampanye, c.terkumpul, c.target_dana, c.id AS cid,
            cat.nama AS kategori
     FROM donations d
     LEFT JOIN campaigns c   ON d.campaign_id = c.id
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE d.id = $donasi_id AND d.status = 'sukses'
     LIMIT 1"
));
if (!$d) { flash('err', 'Data donasi tidak ditemukan.'); go(APP_URL); }

$p         = pct($d['terkumpul'], $d['target_dana']);
$pageTitle = 'Donasi Berhasil — DonasiKita';
require_once '../includes/header.php';
?>

<style>
@keyframes fall {
  from { transform: translateY(-10px) rotate(0deg); opacity: 1; }
  to   { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}
</style>

<div class="container">
  <div class="sukses-box">
    <div class="sukses-icon">✅</div>
    <p style="font-size:1.1rem;font-weight:bold">DONASI BERHASIL!</p>
    <p style="color:#555;margin-top:6px">
      Terima kasih, <strong><?= e($d['nama_donatur']) ?></strong>!<br>
      Kebaikanmu sangat berarti bagi mereka.
    </p>

    <!-- Jumlah donasi — bagian paling menonjol -->
    <div class="sukses-amount"><?= rp($d['jumlah']) ?></div>
    <p style="font-size:.8rem;color:#aaa">telah berhasil disalurkan ✓</p>

    <!-- Detail transaksi -->
    <div class="sukses-rows">
      <div class="sukses-row">
        <span class="lbl">No. Donasi</span>
        <span class="val" style="font-family:monospace">#DON-<?= str_pad($d['id'], 6, '0', STR_PAD_LEFT) ?></span>
      </div>
      <div class="sukses-row">
        <span class="lbl">Program</span>
        <span class="val"><?= e($d['kampanye']) ?></span>
      </div>
      <div class="sukses-row">
        <span class="lbl">Kategori</span>
        <span class="val"><?= e($d['kategori']) ?></span>
      </div>
      <div class="sukses-row">
        <span class="lbl">Metode</span>
        <span class="val"><?= e($d['metode']) ?></span>
      </div>
      <div class="sukses-row">
        <span class="lbl">Waktu</span>
        <span class="val"><?= date('d M Y, H:i', strtotime($d['created_at'])) ?> WIB</span>
      </div>
      <div class="sukses-row">
        <span class="lbl">Status</span>
        <span class="val" style="color:#276749">✓ Sukses</span>
      </div>
    </div>

    <!-- Progress kampanye setelah donasi -->
    <div style="margin-top:18px;text-align:left">
      <p style="font-size:.83rem;color:#888;margin-bottom:4px">Progress kampanye sekarang:</p>
      <div style="display:flex;justify-content:space-between;font-size:.8rem;color:#888;margin-bottom:4px">
        <span><?= rp($d['terkumpul']) ?></span>
        <span><?= $p ?>%</span>
      </div>
      <div class="prog"><div class="prog-fill" style="width:<?= $p ?>%"></div></div>
      <p style="font-size:.78rem;color:#bbb;margin-top:3px">
        dari target <?= rp($d['target_dana']) ?>
      </p>
    </div>

    <!-- Pesan donatur -->
    <?php if (!empty($d['pesan'])): ?>
    <div style="margin-top:14px;background:#f9f9f9;padding:10px 14px;text-align:left;border:1px solid #eee">
      <p style="font-size:.78rem;color:#888;margin-bottom:3px">Pesanmu:</p>
      <p style="font-size:.85rem;color:#555;font-style:italic">"<?= e($d['pesan']) ?>"</p>
    </div>
    <?php endif; ?>

    <!-- Tombol aksi -->
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a href="<?= APP_URL ?>/views/detail.php?id=<?= $d['cid'] ?>" class="btn">Lihat Program</a>
      <a href="<?= APP_URL ?>/views/katalog.php" class="btn btn-outline">Donasi Lain</a>
      <a href="<?= APP_URL ?>" class="btn btn-outline">Beranda</a>
    </div>

    <p style="font-size:.75rem;color:#bbb;margin-top:16px">
      🔒 Donasi tercatat aman di sistem kami. Semoga menjadi berkah! 🤲
    </p>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<script>launchConfetti();</script>
<?php mysqli_close($conn); ?>
