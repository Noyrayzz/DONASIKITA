<?php
require_once '../config/db.php';
$pageTitle = 'Profil Saya — DonasiKita';

// Wajib login
requireLogin();

$u = user();
$user_id = $u['id'];

// Ambil info user dari database
$userInfo = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT id, nama, email, role, created_at FROM users WHERE id = $user_id"
));

// Ambil riwayat donasi user
$donations = mysqli_query(
    $conn,
    "SELECT d.id, d.jumlah, d.metode, d.status, d.created_at, d.pesan,
            c.id AS campaign_id, c.judul AS campaign_name
     FROM donations d
     LEFT JOIN campaigns c ON d.campaign_id = c.id
     WHERE d.user_id = $user_id
     ORDER BY d.created_at DESC"
);

// Total donasi user
$totalDonasi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(jumlah), 0) AS total FROM donations 
     WHERE user_id = $user_id AND status = 'sukses'"
));

// Jumlah kampanye yang didukung
$jmlKampanye = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT campaign_id) AS jml FROM donations 
     WHERE user_id = $user_id AND status = 'sukses'"
));

require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="<?= APP_URL ?>">Beranda</a> / Profil Saya
    </div>

    <h1>PROFIL SAYA</h1>

    <!-- Info User Card -->
    <div style="background:#f9f9f9;border:1px solid #ddd;border-radius:8px;padding:24px;margin-bottom:24px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div>
                <p style="color:#888;font-size:.9rem;margin-bottom:4px">Nama Lengkap</p>
                <p style="font-size:1.1rem;font-weight:bold"><?= e($userInfo['nama']) ?></p>
            </div>
            
            <div>
                <p style="color:#888;font-size:.9rem;margin-bottom:4px">Tipe Akun</p>
                <p style="font-size:1.1rem">
                    <span style="display:inline-block;padding:4px 12px;border-radius:20px;background:<?= $userInfo['role'] === 'admin' ? '#ff6b6b' : '#4ecdc4' ?>;color:white">
                        <?= $userInfo['role'] === 'admin' ? 'Admin' : 'Donatur' ?>
                    </span>
                </p>
            </div>
            <div>
                <p style="color:#888;font-size:.9rem;margin-bottom:4px">Bergabung Sejak</p>
                <p style="font-size:1.1rem"><?= date('d M Y', strtotime($userInfo['created_at'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Statistik Donasi -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px">
        <div style="background:#e8f5e9;border-left:4px solid #4caf50;padding:16px;border-radius:4px">
            <p style="color:#666;font-size:.85rem;margin-bottom:8px">Total Donasi</p>
            <p style="font-size:1.5rem;font-weight:bold;color:#2e7d32"><?= rp($totalDonasi['total']) ?></p>
        </div>
        <div style="background:#e3f2fd;border-left:4px solid #2196f3;padding:16px;border-radius:4px">
            <p style="color:#666;font-size:.85rem;margin-bottom:8px">Kampanye Didukung</p>
            <p style="font-size:1.5rem;font-weight:bold;color:#1565c0"><?= $jmlKampanye['jml'] ?></p>
        </div>
        <div style="background:#fff3e0;border-left:4px solid #ff9800;padding:16px;border-radius:4px">
            <p style="color:#666;font-size:.85rem;margin-bottom:8px">Transaksi Berhasil</p>
            <p style="font-size:1.5rem;font-weight:bold;color:#e65100">
                <?php
                $result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM donations WHERE user_id = $user_id AND status = 'sukses'");
                $cnt = mysqli_fetch_assoc($result);
                echo $cnt['cnt'];
                ?>
            </p>
        </div>
    </div>

    <hr>

    <!-- Riwayat Donasi -->
    <h2 style="margin-top:24px;margin-bottom:16px">RIWAYAT DONASI</h2>

    <?php
    if (mysqli_num_rows($donations) > 0):
    ?>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f5f5f5;border-bottom:2px solid #ddd">
                        <th style="padding:12px;text-align:left">Program</th>
                        <th style="padding:12px;text-align:left">Jumlah</th>
                        <th style="padding:12px;text-align:left">Metode</th>
                        <th style="padding:12px;text-align:left">Status</th>
                        <th style="padding:12px;text-align:left">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($d = mysqli_fetch_assoc($donations)): ?>
                        <tr style="border-bottom:1px solid #eee">
                            <td style="padding:12px">
                                <?php if ($d['campaign_id']): ?>
                                    <a href="<?= APP_URL ?>/views/detail.php?id=<?= $d['campaign_id'] ?>" style="color:#2196f3;text-decoration:none">
                                        <?= e($d['campaign_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#999">[Kampanye Dihapus]</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;font-weight:bold"><?= rp($d['jumlah']) ?></td>
                            <td style="padding:12px"><?= e($d['metode']) ?></td>
                            <td style="padding:12px">
                                <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:.85rem;
              background:<?php
                            if ($d['status'] === 'sukses') echo '#c8e6c9';
                            elseif ($d['status'] === 'pending') echo '#fff9c4';
                            else echo '#ffcdd2';
                            ?>;
              color:<?php
                        if ($d['status'] === 'sukses') echo '#1b5e20';
                        elseif ($d['status'] === 'pending') echo '#f57f17';
                        else echo '#b71c1c';
                    ?>">
                                    <?= ucfirst(e($d['status'])) ?>
                                </span>
                            </td>
                            <td style="padding:12px;color:#888"><?= date('d M Y • H:i', strtotime($d['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php
    else:
    ?>
        <div style="text-align:center;padding:40px 20px;color:#aaa">
            <p style="font-size:1.1rem;margin-bottom:12px">Anda belum melakukan donasi.</p>
            <p style="font-size:.9rem;margin-bottom:20px">Mari mulai membantu sesama dengan berdonasi ke program favorit Anda.</p>
            <a href="<?= APP_URL ?>/views/katalog.php" class="btn">Lihat Program Donasi</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
mysqli_close($conn);
?>