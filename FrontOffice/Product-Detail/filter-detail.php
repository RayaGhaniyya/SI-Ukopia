<?php
session_start();
include("../../Koneksi/koneksi.php");
include("../Component/Loader.php");

$db_connection = $conn;
$id_produk = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil Data Produk
$queryProduk = mysqli_query($db_connection, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
$produk = mysqli_fetch_assoc($queryProduk);

if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan!'); window.history.back();</script>";
    exit;
}

// Ambil Data Variasi
$queryDetail = mysqli_query($db_connection, "
    SELECT dp.*, s.ukuran, g.nama_grind 
    FROM detail_produk dp
    JOIN size s ON dp.id_size = s.id_size
    LEFT JOIN grind_size g ON dp.id_grind = g.id_grind
    WHERE dp.id_produk = '$id_produk'
    ORDER BY s.id_size ASC
");

$variasi = [];
while ($row = mysqli_fetch_assoc($queryDetail)) {
    $variasi[] = $row;
}
$jsonVariasi = json_encode($variasi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $produk['nama_produk'] ?> - Ukopia</title>

    <link rel="stylesheet" href="../assets/css/loader.css">
    <link rel="stylesheet" href="../assets/css/product-detail.css">

    <link rel="stylesheet" href="../assets/css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>


    <main class="product-detail-section">
        <div class="product-layout">

            <div class="left-panel">
                <div class="image-container">
                    <button class="back-button" onclick="window.location.href='../Product/filter.php'">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <img src="<?= $produk['gambar_url'] ?>" alt="<?= $produk['nama_produk'] ?>">
                </div>
            </div>

            <div class="right-panel">
                <div class="content-wrapper">

                    <div class="product-info">
                        <h1 class="product-title"><?= $produk['nama_produk'] ?></h1>
                        <p class="product-price" id="display-price">Rp 0</p>

                        <?php if (!empty($produk['origin'])) : ?>
                            <p><strong>Origin:</strong> <?= $produk['origin'] ?></p>
                        <?php endif; ?>
                        <p style="margin-top:10px; line-height:1.6; color:#555;"><?= nl2br($produk['deskripsi']) ?></p>

                        <div class="product-options">
                            <?php
                            $grinds = array_unique(array_column($variasi, 'nama_grind'));
                            if (!empty($grinds) && $grinds[0] != null):
                            ?>
                                <h4>Grind Size</h4>
                                <div id="grind-options">
                                    <?php foreach ($grinds as $index => $g): ?>
                                        <button class="option-btn grind-btn <?= $index === 0 ? 'active' : '' ?>"
                                            data-value="<?= $g ?>"><?= $g ?></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <h4>Size</h4>
                            <div id="size-options">
                                <?php
                                $sizes = array_unique(array_column($variasi, 'ukuran'));
                                foreach ($sizes as $index => $s):
                                ?>
                                    <button class="option-btn size-btn <?= $index === 0 ? 'active' : '' ?>"
                                        data-value="<?= $s ?>"><?= $s ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="product-quantity">
                            <h4>Quantity</h4>
                            <button class="minus">−</button>
                            <span class="count">1</span>
                            <button class="plus">+</button>
                        </div>
                        <p class="stock">Stock: <span id="stock-display">0</span></p>

                        <div class="product-buttons">
                            <button class="add">Add to Cart</button>
                            <button class="buy">Buy It Now</button>
                        </div>

                        <div class="pickup-info">
                            <p>📍 Pickup available at:</p>
                            <p>Jl. Mastrip No.48, Krajan Timur, Sumbersari, Jember, Jawa Timur</p>
                        </div>
                    </div>

                    <div class="review-section-wrapper">
                        <?php include("../Component/ReviewSection.php"); ?>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/loader.js"></script>

    <script src="../assets/js/toast.js"></script>

    <script>
        const productData = <?= $jsonVariasi ?>;
    </script>

    <script src="../assets/js/product-detail.js"></script>

</body>

</html>