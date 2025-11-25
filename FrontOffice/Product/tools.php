<?php
include("../../Koneksi/koneksi.php");
include("../Component/Loader.php");
include("../Component/NavBar.php");
$current_host = $_SERVER['HTTP_HOST'];

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$id_kategori = 4;
$params = [$id_kategori];
$types = "i";

$sql = "SELECT p.id_produk, p.nama_produk, p.gambar_url, p.link FROM produk p WHERE p.id_kategori = ?";

if (!empty($keyword)) {
    $sql .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $search_param = "%" . $keyword . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}
$sql .= " ORDER BY p.nama_produk ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result_produk = $stmt->get_result();
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<script src="../assets/js/loader.js"></script>
<link rel="stylesheet" href="../assets/css/product.css">

<nav class="secondary-navbar">
    <div class="nav-left">
        <a href="../Product-Cart/index.php" class="cart-icon"><i class="fa-solid fa-basket-shopping"></i></a>
        <div class="dropdown">
            <button class="dropdown-toggle" id="product-btn">Product</button>
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
        <h2 class="logo-title">Brewing Tools</h2>
    </div>
    <div class="nav-right"><?php include("../Component/SearchBar.php"); ?></div>
</nav>

<main class="product-section">
    <div class="product-grid">
        <?php if ($result_produk->num_rows > 0): ?>
            <?php while ($produk = $result_produk->fetch_assoc()):
                $gambar_dinamis = str_replace("localhost", $current_host, $produk['gambar_url']);
                $link_eksternal = htmlspecialchars($produk['link']);
            ?>
                <a href="<?= $link_eksternal ?>" class="product-card-link" target="_blank" rel="noopener noreferrer">
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($gambar_dinamis) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($produk['nama_produk']) ?></h3>
                            <p class="product-price" style="font-size: 0.9em; color: #888;">Lihat di e-commerce</p>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="search-state-container" style="margin-top: 0;">
                <div class="search-state-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="search-state-text">
                    <h3>Produk Tidak Ditemukan</h3>
                    <p>Maaf, kami tidak menemukan produk dengan kata kunci <strong>"<?= htmlspecialchars($keyword) ?>"</strong>.</p>
                    <a href="tools.php" class="btn-reset-search">Lihat Semua Produk</a>
                </div>
            </div>
        <?php endif;
        $stmt->close(); ?>
    </div>

    <?php if (!empty($keyword) && $result_produk->num_rows > 0): ?>
        <div class="search-state-container">
            <div class="search-state-icon"><i class="fa-solid fa-check-circle"></i></div>
            <div class="search-state-text">
                <h3>Hasil Pencarian Ditemukan</h3>
                <p>Menampilkan produk untuk kata kunci <strong>"<?= htmlspecialchars($keyword) ?>"</strong></p>
                <a href="tools.php" class="btn-reset-search">Reset Pencarian</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<script src="../assets/js/product.js"></script>
<?php include("../Component/Footer.php"); ?>