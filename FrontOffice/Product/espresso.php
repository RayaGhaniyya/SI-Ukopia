<?php
include("../../Koneksi/koneksi.php");
include("../Component/Loader.php");
include("../Component/NavBar.php");
$current_host = $_SERVER['HTTP_HOST'];

// --- (1) LOGIKA PENGAMBILAN PRODUK ROBUSTA (ID = 2) ---
$id_kategori_robusta = 2; // <-- DIUBAH
$stmt = $conn->prepare(
    "SELECT 
        p.id_produk, 
        p.nama_produk, 
        p.gambar_url, 
        (SELECT MIN(dp.harga) 
         FROM detail_produk dp 
         WHERE dp.id_produk = p.id_produk) as harga_terendah
    FROM produk p
    WHERE p.id_kategori = ?
    ORDER BY p.nama_produk ASC"
);
$stmt->bind_param("i", $id_kategori_robusta); // <-- DIUBAH
$stmt->execute();
$result_produk = $stmt->get_result();
// --- LOGIKA SELESAI ---
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<script src="../assets/js/loader.js"></script>
<link rel="stylesheet" href="../assets/css/product.css">



<nav class="secondary-navbar">
    <div class="nav-left">
        <a href="../Product-Cart/index.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <div class="dropdown">
            <button class="dropdown-toggle" id="product-btn">
                Product
            </button>
            <div class="dropdown-content" id="product-dropdown">
                <a href="../Product/filter.php">Filter Beans</a>
                <a href="../Product/espresso.php">Espresso Beans</a>
                <a href="../Product/merchandise.php">Merchandise</a>
                <a href="../Product/tools.php">Brewing Tools</a>
                <a href="../Product/approve.php">Ukopia Approve</a>
            </div>
        </div>
    </div>

    <div class="nav-center">
        <h2 class="logo-title">Espresso Roast</h2>
    </div>

    <div class="nav-right">
        <input type="text" class="search-input" placeholder="Search....">
    </div>
</nav>

<main class="product-section">
    <div class="product-grid">

        <?php if ($result_produk->num_rows > 0): ?>
            <?php while ($produk = $result_produk->fetch_assoc()):

                $gambar_dinamis = str_replace("localhost", $current_host, $produk['gambar_url']);

                // (3) LINK DETAIL DIUBAH
                $link_detail = "../Product-Detail/espresso-detail.php?id=" . $produk['id_produk'];

                $harga_display = "Harga Belum Diatur";
                if ($produk['harga_terendah']) {
                    $harga_display = " RP. " . number_format($produk['harga_terendah'], 0, ',', '.');
                }
            ?>
                <a href="<?= $link_detail ?>" class="product-card-link">
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($gambar_dinamis) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($produk['nama_produk']) ?></h3>
                            <p class="product-price"><?= $harga_display ?></p>
                        </div>
                    </div>
                </a>

            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #333; grid-column: 1 / -1; text-align: center;">Belum ada produk yang tersedia.</p>
        <?php endif;
        $stmt->close();
        ?>

    </div>
</main>


<script src="../assets/js/product.js"></script>
<?php
include("../Component/Footer.php");
?>