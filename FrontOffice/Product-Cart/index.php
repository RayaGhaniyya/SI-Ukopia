<?php
session_start();
include("../../Koneksi/koneksi.php");
include("../Component/Loader.php");

if (!isset($_SESSION['customer_uid'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['customer_uid'];
$queryCart = mysqli_query($conn, "
    SELECT k.id_keranjang, k.jumlah, 
           p.nama_produk, p.gambar_url, 
           dp.harga, dp.stok, s.ukuran, g.nama_grind
    FROM keranjang k
    JOIN detail_produk dp ON k.id_detail_produk = dp.id_detail_produk
    JOIN produk p ON dp.id_produk = p.id_produk
    JOIN size s ON dp.id_size = s.id_size
    LEFT JOIN grind_size g ON dp.id_grind = g.id_grind
    WHERE k.uid_akun = '$uid'
    ORDER BY k.tanggal_dibuat DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Ukopia</title>

    <link rel="stylesheet" href="../assets/css/loader.css">
    <link rel="stylesheet" href="../assets/css/product-cart.css">
    <link rel="stylesheet" href="../assets/css/toast.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <script src="../assets/js/loader.js"></script>

    <main class="cart-section">
        <div class="cart-header">
            <a href="../Product/filter.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            Your Cart
        </div>

        <?php if (mysqli_num_rows($queryCart) > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($queryCart)):
                        $subtotal = $item['harga'] * $item['jumlah'];
                    ?>
                        <tr class="cart-item"
                            data-id="<?= $item['id_keranjang'] ?>"
                            data-price="<?= $item['harga'] ?>"
                            data-stock="<?= $item['stok'] ?>">

                            <td>
                                <div class="product-info">
                                    <input type="checkbox" class="custom-checkbox item-check" checked>

                                    <div class="product-img">
                                        <img src="<?= $item['gambar_url'] ?>" alt="<?= $item['nama_produk'] ?>">
                                    </div>
                                    <div class="product-details">
                                        <h3><?= $item['nama_produk'] ?></h3>
                                        <p class="price-tag">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                        <p class="variant-info">
                                            <strong>Size:</strong> <?= $item['ukuran'] ?>
                                            <?php if ($item['nama_grind']): ?>
                                                | <strong>Grind:</strong> <?= $item['nama_grind'] ?>
                                            <?php endif; ?>
                                            | <span style="color:#888; font-size:0.8em;">Sisa: <?= $item['stok'] ?></span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="quantity">
                                    <button class="qty-btn minus">−</button>
                                    <span class="qty-count"><?= $item['jumlah'] ?></span>
                                    <button class="qty-btn plus">+</button>
                                    <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                            <td class="product-total">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="summary-left">
                    <input type="checkbox" id="select-all" class="custom-checkbox" checked>
                    <label for="select-all" class="select-all-label">Pilih Semua</label>
                </div>
                <div class="summary-center">
                    <p><strong>Estimated total:</strong> <span id="cart-total">Rp 0</span></p>
                </div>
                <div class="summary-right">
                    <button class="checkout-btn" id="btn-checkout">Check out</button>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <i class="fa-solid fa-basket-shopping"></i>
                <h2>Keranjangmu masih kosong</h2>
                <p>Yuk, isi dengan kopi favoritmu!</p>
                <a href="../Product/filter.php" class="shop-btn">Belanja Sekarang</a>
            </div>
        <?php endif; ?>

    </main>

    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/product-cart.js"></script>

</body>

</html>