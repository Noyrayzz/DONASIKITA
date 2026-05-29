<?php

session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_donasi');

// ── Auto-detect APP_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

if (strpos($_SERVER['HTTP_HOST'], 'pinggy.link') !== false) {
    define('APP_URL', $protocol . $_SERVER['HTTP_HOST']);
} else {

    $_base = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__));
    $_base = str_replace('\\', '/', $_base);
    define('APP_URL', $protocol . $_SERVER['HTTP_HOST'] . $_base);
}

define('UPLOAD_PATH', dirname(__DIR__) . '/assets/uploads/');
define('UPLOAD_URL',  APP_URL . '/assets/uploads/');
define('UPLOAD_PATH', dirname(__DIR__) . '/assets/uploads/');
define('UPLOAD_URL',  APP_URL . '/assets/uploads/');

// ── Koneksi MySQL
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('<p style="color:red;padding:20px">
        Koneksi database gagal: ' . mysqli_connect_error() . '<br>
        Pastikan MySQL menyala dan database <b>db_donasi</b> sudah diimport!
    </p>');
}
mysqli_set_charset($conn, 'utf8mb4');


// AUTO UPDATE STATUS KAMPANYE BERDASARKAN TANGGAL
function autoUpdateCampaignStatus($conn)
{
    $today = date('Y-m-d');

    // Update kampanye aktif yang sudah melewati tgl_selesai menjadi 'selesai'
    mysqli_query(
        $conn,
        "UPDATE campaigns 
         SET status = 'selesai' 
         WHERE status = 'aktif' 
         AND tgl_selesai < '$today'"
    );

    return true;
}

// Jalankan auto update status pada setiap page load
autoUpdateCampaignStatus($conn);

// Sanitasi output (anti XSS)
function e($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Format Rupiah
function rp($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

// Hitung persentase progress
function pct($terkumpul, $target)
{
    return $target > 0 ? min(100, round($terkumpul / $target * 100)) : 0;
}

// Hitung sisa hari deadline
function sisaHari($tgl)
{
    $diff = strtotime($tgl) - time();
    return $diff > 0 ? ceil($diff / 86400) : 0;
}

// Ambil data user dari session
function user()
{
    return $_SESSION['user'] ?? null;
}
function isLogin()
{
    return isset($_SESSION['user']);
}
function isAdmin()
{
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}

// Simpan flash message
function flash($t, $m)
{
    $_SESSION['flash'] = ['t' => $t, 'm' => $m];
}

// Ambil & hapus flash message
function getFlash()
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// Redirect
function go($url)
{
    header('Location: ' . $url);
    exit;
}

// Wajib login
function requireLogin()
{
    if (!isLogin()) {
        flash('err', 'Silakan login terlebih dahulu.');
        go(APP_URL . '/auth/login.php');
    }
}

// Wajib admin
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        flash('err', 'Akses ditolak.');
        go(APP_URL);
    }
}

// Catat ke activity_logs
function log_aksi($conn, $aksi, $ket = '')
{
    $uid = user()['id'] ?? 'NULL';
    $a   = mysqli_real_escape_string($conn, $aksi);
    $k   = mysqli_real_escape_string($conn, $ket);
    mysqli_query($conn, "INSERT INTO activity_logs(user_id,aksi,keterangan) VALUES($uid,'$a','$k')");
}

// ── QUICK DONATE FUNCTION ──

/**
 * Proses Quick Donate (insert donation cepat dengan nominal pilihan)
 * Return: array ['success' => bool, 'message' => string]
 */
function processQuickDonate($conn, $campaign_id, $jumlah)
{
    // Validasi user sudah login
    if (!isLogin()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu'];
    }

    $user_id = (int)user()['id'];
    $user_nama = user()['nama'];
    $campaign_id = (int)$campaign_id;
    $jumlah = (float)$jumlah;
    $metode = 'Transfer';
    $pesan = 'Donasi cepat lewat fitur Quick Donate';

    // Validasi nilai donasi
    if ($jumlah < 100) {
        return ['success' => false, 'message' => 'Nominal minimal Rp100'];
    }

    if ($jumlah > 1000000) {
        return ['success' => false, 'message' => 'Nominal maksimal Rp1.000.000'];
    }

    // Cek kampanye ada
    $query_cek = "SELECT id FROM campaigns WHERE id = ?";
    $stmt_cek = $conn->prepare($query_cek);
    if (!$stmt_cek) {
        return ['success' => false, 'message' => 'Gagal cek kampanye'];
    }
    $stmt_cek->bind_param('i', $campaign_id);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();
    $campaign = $res_cek->fetch_assoc();
    $stmt_cek->close();

    if (!$campaign) {
        return ['success' => false, 'message' => 'Kampanye tidak ditemukan'];
    }

    // Insert donasi baru
    $query_insert = "INSERT INTO donations 
                     (campaign_id, user_id, nama_donatur, jumlah, pesan, metode, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 'sukses', NOW())";

    $stmt_insert = $conn->prepare($query_insert);
    if (!$stmt_insert) {
        return ['success' => false, 'message' => 'Gagal menyiapkan query'];
    }

    $stmt_insert->bind_param('iissss', $campaign_id, $user_id, $user_nama, $jumlah, $pesan, $metode);

    if (!$stmt_insert->execute()) {
        return ['success' => false, 'message' => 'Gagal menyimpan donasi'];
    }

    $stmt_insert->close();

    // Update terkumpul campaign
    $query_update = "UPDATE campaigns SET terkumpul = terkumpul + ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    if ($stmt_update) {
        $stmt_update->bind_param('di', $jumlah, $campaign_id);
        $stmt_update->execute();
        $stmt_update->close();
    }

    // Log aksi
    log_aksi($conn, 'Quick Donate', "Campaign ID: $campaign_id | Nominal: " . rp($jumlah));

    return ['success' => true, 'message' => 'Terima kasih! Donasi Anda berhasil diproses.'];
}
