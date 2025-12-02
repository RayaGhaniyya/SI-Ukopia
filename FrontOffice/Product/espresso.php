<?php
include("../../Koneksi/koneksi.php");
include("../Component/Loader.php");
include("../Component/NavBar.php");
include("../Component/pagination.php"); // Pastikan file ini ada
$current_host = $_SERVER['HTTP_HOST'];

// --- 1. SETUP VARIABEL ---
$id_kategori = 2; // ARABICA
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; // 50 Produk per halaman
$offset = ($page - 1) * $limit;

// --- 2. BASE URL (Untuk Pagination) ---
$baseUrl = "?";
if (!empty($keyword)) $baseUrl .= "keyword=" . urlencode($keyword) . "&";
if ($sort != 'default') $baseUrl .= "sort=" . urlencode($sort) . "&";

// --- 3. SIAPKAN QUERY FILTER ---
$whereClause = "WHERE p.id_kategori = ?";
$params = [$id_kategori];
$types = "i";

if (!empty($keyword)) {
    $whereClause .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $search_param = "%" . $keyword . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// --- 4. HITUNG TOTAL DATA ---
$countSql = "SELECT COUNT(*) as total FROM produk p $whereClause";
$stmtCount = $conn->prepare($countSql);
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalRows = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
$stmtCount->close();

// --- 5. TENTUKAN SORTING ---
$orderBy = "ORDER BY p.nama_produk ASC"; // Default A-Z

if ($sort == 'price_asc') {
    $orderBy = "ORDER BY harga_terendah ASC";
} elseif ($sort == 'price_desc') {
    $orderBy = "ORDER BY harga_terendah DESC";
} elseif ($sort == 'newest') {
    $orderBy = "ORDER BY p.id_produk DESC";
}

// --- 6. QUERY UTAMA ---
$sql = "SELECT 
            p.id_produk, 
            p.nama_produk, 
            p.gambar_url, 
            (SELECT MIN(dp.harga) FROM detail_produk dp WHERE dp.id_produk = p.id_produk) as harga_terendah
        FROM produk p
        $whereClause
        $orderBy
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

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
        <h2 class="logo-title">Filter Roast</h2>
    </div>
    <div class="nav-right"><?php include("../Component/SearchBar.php"); ?></div>
</nav>

<main class="product-section">

    <?php if ($totalRows > 0): ?>
        <div class="product-header-control">
            <div class="result-count">
                Menampilkan <strong><?= $result_produk->num_rows ?></strong> dari <strong><?= $totalRows ?></strong> produk
            </div>

            <select class="sort-dropdown" onchange="location = this.value;">
                <option value="?keyword=<?= $keyword ?>&sort=default" <?= $sort == 'default' ? 'selected' : '' ?>>Urutkan: A - Z</option>
                <option value="?keyword=<?= $keyword ?>&sort=price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Harga: Terendah - Tertinggi</option>
                <option value="?keyword=<?= $keyword ?>&sort=price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Harga: Tertinggi - Terendah</option>
                <option value="?keyword=<?= $keyword ?>&sort=newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Terbaru</option>
            </select>
        </div>
    <?php endif; ?>

    <div class="product-grid">
        <?php if ($result_produk->num_rows > 0): ?>
            <?php while ($produk = $result_produk->fetch_assoc()):
                $gambar_dinamis = str_replace("localhost", $current_host, $produk['gambar_url']);
                $link_detail = "../Product-Detail/filter-detail.php?id=" . $produk['id_produk'];
                $harga_display = $produk['harga_terendah'] ? "Rp " . number_format($produk['harga_terendah'], 0, ',', '.') : "Harga Belum Diatur";
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

            <div class="search-state-container" style="margin-top: 0;">
                <div class="search-state-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="search-state-text">
                    <h3>Produk Tidak Ditemukan</h3>
                    <p>Maaf, kami tidak menemukan produk dengan kata kunci <strong>"<?= htmlspecialchars($keyword) ?>"</strong>.</p>
                    <a href="filter.php" class="btn-reset-search">Lihat Semua Produk</a>
                </div>
            </div>

        <?php endif;
        $stmt->close(); ?>
    </div>

    <?php renderPaginator($totalPages, $page, $baseUrl); ?>

    <?php if (!empty($keyword) && $result_produk->num_rows > 0): ?>
        <div class="search-state-container">
            <div class="search-state-icon"><i class="fa-solid fa-check-circle"></i></div>
            <div class="search-state-text">
                <h3>Hasil Pencarian Ditemukan</h3>
                <p>Menampilkan produk untuk kata kunci <strong>"<?= htmlspecialchars($keyword) ?>"</strong></p>
                <a href="filter.php" class="btn-reset-search">Reset Pencarian</a>
            </div>
        </div>
    <?php endif; ?>

</main>

<script src="../assets/js/product.js"></script>
<?php include("../Component/Footer.php"); ?>