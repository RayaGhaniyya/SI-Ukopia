<?php
include("../../Koneksi/koneksi.php");
include("../Component/NavBar.php");
include("../Component/Loader.php");

// Pagination
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Count total galleries
$countQuery = "SELECT COUNT(*) as total FROM galery";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch galleries with images
$query = "
    SELECT 
        g.id_galery,
        g.judul,
        g.deskripsi,
        g.tanggal
    FROM galery g
    ORDER BY g.tanggal DESC, g.id_galery DESC
    LIMIT $itemsPerPage OFFSET $offset
";

$result = mysqli_query($conn, $query);
$galleries = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Fetch images for this gallery
    $id = $row['id_galery'];
    $imgQuery = "SELECT gambar FROM detail_galery WHERE id_galery = $id LIMIT 4";
    $imgResult = mysqli_query($conn, $imgQuery);

    $images = [];
    while ($img = mysqli_fetch_assoc($imgResult)) {
        $images[] = $img['gambar'];
    }

    $row['images'] = $images;
    $galleries[] = $row;
}
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<script src="../assets/js/loader.js"></script>
<link rel="stylesheet" href="../assets/css/galery.css">

<section class="galery-section">
    <div class="container">
        <h1 class="section-title pt-3">Gallery</h1>

        <?php if (count($galleries) > 0): ?>
            <div class="galery-content">
                <?php
                $index = 0;
                foreach ($galleries as $gallery):
                    $isEven = $index % 2 == 0;
                    $tanggalFormat = date('d-m-y', strtotime($gallery['tanggal']));
                ?>

                    <!-- Pattern: Gambar dulu (untuk index genap: 0, 2, 4...) -->
                    <?php if ($isEven): ?>
                        <div class="galery-images">
                            <div class="img-grid">
                                <?php
                                // Tampilkan max 4 gambar
                                $imageCount = min(4, count($gallery['images']));
                                for ($i = 0; $i < $imageCount; $i++):
                                    $imgPath = "../../BackOffice/" . $gallery['images'][$i];
                                ?>
                                    <img src="<?php echo htmlspecialchars($imgPath); ?>"
                                        alt="<?php echo htmlspecialchars($gallery['judul']); ?>"
                                        onerror="this.src='../assets/img/placeholder.jpg'">
                                <?php endfor; ?>

                                <?php
                                // Fill dengan placeholder jika kurang dari 4
                                for ($i = $imageCount; $i < 4; $i++):
                                ?>
                                    <img src="../assets/img/placeholder.jpg" alt="Placeholder">
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Text Content -->
                    <div class="galery-text">
                        <h3><?php echo htmlspecialchars($gallery['judul']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($gallery['deskripsi'])); ?></p>
                        <span class="date"><?php echo $tanggalFormat; ?></span>
                    </div>

                    <!-- Pattern: Gambar di belakang (untuk index ganjil: 1, 3, 5...) -->
                    <?php if (!$isEven): ?>
                        <div class="galery-images">
                            <div class="img-grid">
                                <?php
                                $imageCount = min(4, count($gallery['images']));
                                for ($i = 0; $i < $imageCount; $i++):
                                    $imgPath = "../../BackOffice/" . $gallery['images'][$i];
                                ?>
                                    <img src="<?php echo htmlspecialchars($imgPath); ?>"
                                        alt="<?php echo htmlspecialchars($gallery['judul']); ?>"
                                        onerror="this.src='../assets/img/placeholder.jpg'">
                                <?php endfor; ?>

                                <?php
                                for ($i = $imageCount; $i < 4; $i++):
                                ?>
                                    <img src="../assets/img/placeholder.jpg" alt="Placeholder">
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php
                    $index++;
                endforeach;
                ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <div class="pagination-numbers">
                        <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);

                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>"
                                class="pagination-number <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-gallery">
                <i class="fas fa-images"></i>
                <h3>Belum Ada Galeri</h3>
                <p>Galeri akan segera hadir. Stay tuned!</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<script src="../assets/js/galery.js"></script>

<?php include("../Component/Footer.php"); ?>