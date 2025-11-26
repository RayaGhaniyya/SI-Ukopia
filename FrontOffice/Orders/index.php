<?php
session_start();
include("../../Koneksi/koneksi.php");

// 1. Cek Login
if (!isset($_SESSION['customer_uid'])) {
    header('Location: ../auth/login.php');
    exit;
}

$customer_uid = $_SESSION['customer_uid'];

// 2. Ambil Semua Transaksi
$queryTrx = mysqli_query($conn, "
    SELECT t.*, 
           (SELECT p.gambar_url FROM detail_transaksi dt 
            JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk 
            JOIN produk p ON dp.id_produk = p.id_produk 
            WHERE dt.id_transaksi = t.id_transaksi LIMIT 1) as gambar_produk,
           
           (SELECT p.nama_produk FROM detail_transaksi dt 
            JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk 
            JOIN produk p ON dp.id_produk = p.id_produk 
            WHERE dt.id_transaksi = t.id_transaksi LIMIT 1) as nama_produk_utama,
           
           (SELECT COUNT(*) - 1 FROM detail_transaksi dt 
            WHERE dt.id_transaksi = t.id_transaksi) as sisa_item
    FROM transaksi t
    WHERE t.uid_customer = '$customer_uid'
    ORDER BY t.tanggal_pesan DESC
");

// 3. Pisahkan ke 3 Tab (Array Filtering)
$orders_process = [];
$orders_shipping = [];
$orders_history = [];

while ($trx = mysqli_fetch_assoc($queryTrx)) {
    $s = $trx['status_pesanan'];
    if ($s == 'Menunggu Pembayaran' || $s == 'Sudah Dibayar' || $s == 'Diproses') {
        $orders_process[] = $trx;
    } elseif ($s == 'Dikirim') {
        $orders_shipping[] = $trx;
    } else {
        $orders_history[] = $trx; // Selesai, Batal, Kadaluarsa, Pengajuan Batal
    }
}

include("../Component/Loader.php");
include("../Component/NavBar.php");

// Fungsi Helper Render Item HTML
function renderTransactionItem($trx)
{
    $statusClass = 'badge-secondary';
    if ($trx['status_pesanan'] == 'Menunggu Pembayaran') $statusClass = 'badge-warning text-dark';
    elseif ($trx['status_pesanan'] == 'Sudah Dibayar' || $trx['status_pesanan'] == 'Diproses') $statusClass = 'badge-info text-dark';
    elseif ($trx['status_pesanan'] == 'Dikirim') $statusClass = 'badge-primary';
    elseif ($trx['status_pesanan'] == 'Selesai') $statusClass = 'badge-success';
    elseif (strpos($trx['status_pesanan'], 'Batal') !== false || $trx['status_pesanan'] == 'Kadaluarsa') $statusClass = 'badge-danger';

    $gambar_mentah = $trx['gambar_produk'] ?? '';
    $img = str_replace("localhost", $_SERVER['HTTP_HOST'], $gambar_mentah);
    if (empty($img)) $img = "../assets/img/default-product.png";

    $date = date('d M Y, H:i', strtotime($trx['tanggal_pesan']));
    $deadline = date('Y-m-d H:i:s', strtotime($trx['tanggal_pesan'] . ' +15 minutes'));

    echo '<div class="transaction-item">';
    echo '<div class="trx-header">';
    echo '<div>';
    echo '<span class="trx-date"><i class="far fa-calendar-alt"></i> ' . $date . '</span>';
    if ($trx['status_pesanan'] == 'Menunggu Pembayaran') {
        echo '<span class="badge bg-danger ms-2 countdown-timer" data-deadline="' . $deadline . '">Menghitung...</span>';
    }
    echo '</div>';
    echo '<span class="trx-status ' . $statusClass . '">' . $trx['status_pesanan'] . '</span>';
    echo '</div>';

    echo '<div class="trx-body">';
    echo '<div class="trx-img"><img src="' . $img . '" alt="Produk"></div>';
    echo '<div class="trx-info">';
    echo '<h4 class="trx-title">' . $trx['nama_produk_utama'] . '</h4>';
    if ($trx['sisa_item'] > 0) echo '<p class="trx-more">+ ' . $trx['sisa_item'] . ' produk lainnya</p>';
    echo '<p class="trx-total">Total: Rp ' . number_format($trx['total_pembayaran'], 0, ',', '.') . '</p>';
    echo '</div>';
    echo '</div>';

    echo '<div class="trx-footer">';
    // Tombol Aksi Dinamis
    if ($trx['status_pesanan'] == 'Menunggu Pembayaran') {
        echo '<button class="btn btn-outline-danger btn-sm me-2" onclick="cancelOrder(' . $trx['id_transaksi'] . ')">Batalkan</button>';
        echo '<button class="btn btn-dark btn-sm btn-pay-now" onclick="payNow(\'' . $trx['snap_token'] . '\')">Bayar Sekarang</button>';
    } elseif ($trx['status_pesanan'] == 'Sudah Dibayar') {
        echo '<button class="btn btn-outline-danger btn-sm me-2" onclick="cancelOrder(' . $trx['id_transaksi'] . ')">Batalkan Pesanan</button>';
    } elseif ($trx['status_pesanan'] == 'Dikirim') {
        echo '<button class="btn btn-success btn-sm me-2" onclick="completeOrder(' . $trx['id_transaksi'] . ')"><i class="fas fa-check"></i> Pesanan Diterima</button>';
    }
    echo '<button class="btn btn-outline-secondary btn-sm ms-2" onclick="showDetail(' . $trx['id_transaksi'] . ')">Lihat Detail</button>';
    echo '</div>';
    echo '</div>';
}
?>

<link rel="stylesheet" href="../assets/css/toast.css">
<link rel="stylesheet" href="../assets/css/orders.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/loader.css">

<script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="SB-Mid-client-XXXXXXXXXXXXXXXXXXXX"></script>

<script src="../assets/js/loader.js"></script>

<div class="profile-body" style="min-height: 100vh; background-color: #f4f7f6;">

    <div class="orders-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="profile-title" style="font-size: 1.8rem;">Pesanan Saya</h1>

                <?php
                // LOGIKA TOMBOL BACK DINAMIS
                $source = isset($_GET['source']) ? $_GET['source'] : '';

                if ($source == 'profile') {
                    // Jika datang dari Profile
                    $back_link = '../Profile/index.php';
                    $back_text = 'Kembali ke Profil';
                    $back_icon = 'fa-user';
                } else {
                    // Jika dari Navbar / Checkout / Lainnya (Default)
                    $back_link = '../HomePage/index.php';
                    $back_text = 'Kembali';
                    $back_icon = 'fa-arrow-left';
                }
                ?>

                <a href="<?= $back_link ?>" class="btn-back-home">
                    <i class="fas <?= $back_icon ?>"></i> <?= $back_text ?>
                </a>
            </div>
        </div>
    </div>

    <div class="container profile-content-container">

        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="process-tab" data-bs-toggle="tab" data-bs-target="#process" type="button">
                    Diproses <span class="badge bg-secondary rounded-pill ms-1"><?= count($orders_process) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button">
                    Dikirim <span class="badge bg-secondary rounded-pill ms-1"><?= count($orders_shipping) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
                    Riwayat
                </button>
            </li>
        </ul>

        <div class="tab-content" id="orderTabsContent">

            <div class="tab-pane fade show active" id="process" role="tabpanel">
                <div class="transaction-list">
                    <?php if (count($orders_process) > 0): ?>
                        <?php foreach ($orders_process as $item) renderTransactionItem($item); ?>
                    <?php else: ?>
                        <div class="empty-tab">
                            <i class="fas fa-box-open"></i> Tidak ada pesanan yang sedang diproses.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="shipping" role="tabpanel">
                <div class="transaction-list">
                    <?php if (count($orders_shipping) > 0): ?>
                        <?php foreach ($orders_shipping as $item) renderTransactionItem($item); ?>
                    <?php else: ?>
                        <div class="empty-tab">
                            <i class="fas fa-truck"></i> Tidak ada pesanan dalam pengiriman.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="history" role="tabpanel">
                <div class="transaction-list">
                    <?php if (count($orders_history) > 0): ?>
                        <?php foreach ($orders_history as $item) renderTransactionItem($item); ?>
                    <?php else: ?>
                        <div class="empty-tab">
                            <i class="fas fa-history"></i> Belum ada riwayat pesanan selesai.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="trxDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/toast.js"></script>
<script src="../assets/js/orders.js"></script>
</body>

</html>