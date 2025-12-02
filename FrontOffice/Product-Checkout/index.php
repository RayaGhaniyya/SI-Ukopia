<?php
session_start();
include("../../Koneksi/koneksi.php");
include("../Component/Loader.php");
if (!isset($_SESSION['customer_uid'])) {
    header("Location: ../auth/login.php");
    exit;
}
$uid = $_SESSION['customer_uid'];
$items = [];
$subtotal = 0;
$back_url = '../Product-Cart/index.php'; // Default: Kembali ke Keranjang
$back_text = 'Kembali ke Keranjang';
if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'buy_now' && isset($_SESSION['buy_now_item'])) {
    $id_varian = $_SESSION['buy_now_item']['id_detail_produk'];
    $qty_beli  = $_SESSION['buy_now_item']['qty'];
    $queryItem = mysqli_query($conn, "
        SELECT 
            dp.id_detail_produk, dp.harga, dp.stok,
            p.nama_produk, p.gambar_url, p.id_produk, p.id_kategori,
            s.ukuran, g.nama_grind
        FROM detail_produk dp
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN size s ON dp.id_size = s.id_size
        LEFT JOIN grind_size g ON dp.id_grind = g.id_grind
        WHERE dp.id_detail_produk = '$id_varian'
    ");
    if ($row = mysqli_fetch_assoc($queryItem)) {
        $row['jumlah'] = $qty_beli;
        $items[] = $row;
        $subtotal += ($row['harga'] * $qty_beli);
        $cat = $row['id_kategori'];
        $pid = $row['id_produk'];
        $back_text = 'Kembali ke Produk';
        if ($cat == 1) { // Filter
            $back_url = "../Product-Detail/filter-detail.php?id=$pid";
        } elseif ($cat == 2) { // Espresso
            $back_url = "../Product-Detail/espresso-detail.php?id=$pid";
        } elseif ($cat == 3) { // Merchandise
            $back_url = "../Product-Detail/merchandise-detail.php?id=$pid";
        } else {
            $back_url = "../Product/filter.php";
        }
    }
} else {
    $queryCart = mysqli_query($conn, "
        SELECT k.*, p.nama_produk, p.gambar_url, dp.harga, s.ukuran, g.nama_grind 
        FROM keranjang k
        JOIN detail_produk dp ON k.id_detail_produk = dp.id_detail_produk
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN size s ON dp.id_size = s.id_size
        LEFT JOIN grind_size g ON dp.id_grind = g.id_grind
        WHERE k.uid_akun = '$uid'
    ");
    if (mysqli_num_rows($queryCart) == 0) {
        echo "<script>alert('Keranjang kosong!'); window.location.href='../Product/filter.php';</script>";
        exit;
    }
    while ($row = mysqli_fetch_assoc($queryCart)) {
        $items[] = $row;
        $subtotal += ($row['harga'] * $row['jumlah']);
    }
}
$queryAlamat = mysqli_query($conn, "SELECT * FROM alamat_customer WHERE uid_customer = '$uid' ORDER BY is_utama DESC");
$alamatList = [];
while ($row = mysqli_fetch_assoc($queryAlamat)) {
    $alamatList[] = $row;
}
$ongkir = 20000;
$biaya_layanan = 2500;
$total_bayar = $subtotal + $ongkir + $biaya_layanan;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ukopia</title>
    <link rel="stylesheet" href="../assets/css/loader.css">
    <link rel="stylesheet" href="../assets/css/product-checkout.css">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="SB-Mid-server-XXHpirkXbiie0mBkOPTOJBp9"></script>
</head>
<body>
    <script src="../assets/js/loader.js"></script>
    <main class="checkout-section">
        <div class="checkout-container">
            <div class="checkout-left">
                <button class="back-button" onclick="window.location.href='<?= $back_url ?>'">
                    <i class="fa-solid fa-arrow-left"></i> <?= $back_text ?>
                </button>
                <form id="checkoutForm" class="checkout-form">
                    <h3>Contact</h3>
                    <input type="email" value="<?php echo $_SESSION['customer_nama']; ?> (Email terdaftar)" readonly style="background:#eee; cursor:not-allowed;">
                    <h3>Delivery Method</h3>
                    <div class="delivery-section">
                        <label class="delivery-option">
                            <input type="radio" name="delivery_type" value="ship" checked>
                            <div class="option-content">
                                <span>Ship (JNE/J&T)</span>
                                <i class="fa-solid fa-truck"></i>
                            </div>
                        </label>
                        <label class="delivery-option">
                            <input type="radio" name="delivery_type" value="pickup">
                            <div class="option-content">
                                <span>Pick up (Ambil di Toko)</span>
                                <i class="fa-solid fa-store"></i>
                            </div>
                        </label>
                    </div>
                    <h3>Alamat Pengiriman</h3>
                    <?php if (count($alamatList) > 0): ?>
                        <div class="address-list">
                            <?php foreach ($alamatList as $index => $addr): ?>
                                <label class="address-card-option">
                                    <input type="radio" name="id_alamat" value="<?= $addr['id_alamat'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                    <div class="address-details">
                                        <strong><?= htmlspecialchars($addr['label_alamat']) ?> (<?= htmlspecialchars($addr['nama_penerima']) ?>)</strong>
                                        <p><?= htmlspecialchars($addr['no_telepon']) ?></p>
                                        <p class="address-text">
                                            <?= htmlspecialchars($addr['alamat_lengkap']) ?>,
                                            <?= htmlspecialchars($addr['kota']) ?>,
                                            <?= htmlspecialchars($addr['provinsi']) ?>
                                            <?= htmlspecialchars($addr['kode_pos']) ?>
                                        </p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <a href="../Profile/index.php" class="btn-manage-address">
                            <i class="fa-solid fa-plus"></i> Kelola / Tambah Alamat Baru
                        </a>
                    <?php else: ?>
                        <div class="no-address-alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <p>Kamu belum memiliki alamat tersimpan.</p>
                            <a href="../Profile/index.php" class="btn-add-address">Tambah Alamat Sekarang</a>
                        </div>
                        <input type="hidden" name="id_alamat" value="" required>
                    <?php endif; ?>
                    <input type="hidden" name="total_bayar" value="<?= $total_bayar ?>">
                    <input type="hidden" name="ongkir" value="<?= $ongkir ?>">
                    <button type="button" id="pay-button" class="btn-submit">Bayar Sekarang (Rp <?= number_format($total_bayar, 0, ',', '.') ?>)</button>
                </form>
            </div>
            <div class="checkout-right">
                <h3>Produk Dipesan (<?= count($items) ?>)</h3>
                <div class="product-summary">
                    <?php foreach ($items as $item): ?>
                        <div class="product-item">
                            <div class="img-box">
                                <img src="<?= $item['gambar_url'] ?>" alt="<?= $item['nama_produk'] ?>">
                                <span class="qty-badge"><?= $item['jumlah'] ?></span>
                            </div>
                            <div class="info-box">
                                <p class="name"><?= $item['nama_produk'] ?></p>
                                <p class="variant"><?= $item['ukuran'] ?><?= $item['nama_grind'] ? ', ' . $item['nama_grind'] : '' ?></p>
                            </div>
                            <div class="subtotal">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <h3 style="margin-bottom: 10px;">Rincian Pembayaran</h3>
                <div class="payment-summary">
                    <p><span>Subtotal Pesanan</span><span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></p>
                    <p><span>Ongkos Kirim (Flat)</span><span>Rp <?= number_format($ongkir, 0, ',', '.') ?></span></p>
                    <p><span>Biaya Layanan</span><span>Rp <?= number_format($biaya_layanan, 0, ',', '.') ?></span></p>
                    <p class="total"><span>Total Pembayaran</span><span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span></p>
                </div>
            </div>
        </div>
    </main>
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/product-checkout.js"></script>
</body>
</html>

