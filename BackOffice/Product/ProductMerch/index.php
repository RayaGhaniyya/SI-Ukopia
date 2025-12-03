<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php");
$current_host = $_SERVER['HTTP_HOST'];


$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';
$base_url_pagin = '?';
$params = [];
$types = "";


$where_conditions = ["p.id_kategori = 3"];

if ($search_term != '') {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

$where_sql = " WHERE " . implode(" AND ", $where_conditions);


$count_query = "SELECT COUNT(DISTINCT p.id_produk) as total FROM produk p $where_sql";
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();


$order_by_sql = " ORDER BY p.id_produk DESC LIMIT ? OFFSET ?";
$data_query = "
    SELECT 
        p.id_produk, p.nama_produk, p.gambar_url, p.deskripsi,
        k.nama_kategori,
        GROUP_CONCAT(pg.gambar_url SEPARATOR ',') as list_galeri
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id_kategori
    LEFT JOIN produk_galeri pg ON p.id_produk = pg.id_produk
    $where_sql
    GROUP BY p.id_produk
    $order_by_sql
";

$data_params = $params;
$data_params[] = $limit;
$data_params[] = $offset;
$data_types = $types . "ii";

$stmt_data = $conn->prepare($data_query);
$stmt_data->bind_param($data_types, ...$data_params);
$stmt_data->execute();
$result = $stmt_data->get_result();
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-tshirt"></i> Manajemen Merchandise</h1>
            <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotification('<?= addslashes($_SESSION['message']) ?>', '<?= $_SESSION['message_type'] ?>');
                });
            </script>
            <?php unset($_SESSION['message']);
            unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-header">
                <h2>Data Merchandise (Total: <?= $total_rows ?>)</h2>
                <form action="index.php" method="GET" class="search-group">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Thumbnail</th>
                            <th width="20%">Nama Produk</th>
                            <th width="15%">Galeri</th>
                            <th width="35%">Deskripsi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0):
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $gambar_utama = str_replace("localhost", $current_host, $row['gambar_url']);

                                
                                $gallery_array = [];
                                if (!empty($row['list_galeri'])) {
                                    $raw_urls = explode(',', $row['list_galeri']);
                                    foreach ($raw_urls as $url) {
                                        $gallery_array[] = str_replace("localhost", $current_host, $url);
                                    }
                                }
                                
                                $gallery_json = htmlspecialchars(json_encode($gallery_array), ENT_QUOTES, 'UTF-8');
                                $jumlah_foto = count($gallery_array);
                        ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <img src="<?= $gambar_utama ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['nama_produk']) ?></strong></td>
                                    <td>
                                        <?php if ($jumlah_foto > 0): ?>
                                            <button type="button" class="btn btn-info btn-sm" onclick="openGalleryModal('<?= htmlspecialchars($row['nama_produk']) ?>', <?= $gallery_json ?>)">
                                                <i class="fas fa-images"></i> Lihat (<?= $jumlah_foto ?>)
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size:12px;">- Kosong -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= mb_strimwidth(htmlspecialchars($row['deskripsi']), 0, 50, "...") ?></td>
                                    <td>
                                        <a href="update.php?id=<?= $row['id_produk'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <a href="action/delete.php?id=<?= $row['id_produk'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <?php renderPaginator($total_pages, $current_page, $base_url_pagin); ?>
            </div>
        </div>
    </div>
</div>

<div id="galleryModal" class="popup-overlay">
    <div class="popup-card">
        <div class="popup-header">
            <h3 id="galleryTitle">Galeri Produk</h3>
            <button class="popup-close" onclick="closeGalleryModal()">&times;</button>
        </div>

        <div class="popup-body">
            <div id="galleryGrid" class="gallery-grid">
            </div>
        </div>

        <div class="popup-footer">
            <button onclick="closeGalleryModal()" class="btn btn-secondary btn-sm">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openGalleryModal(title, images) {
        document.getElementById('galleryTitle').textContent = "Galeri: " + title;
        const container = document.getElementById('galleryGrid');
        container.innerHTML = ''; 

        if (images.length > 0) {
            images.forEach(src => {
                
                const wrapper = document.createElement('div');
                wrapper.className = 'gallery-item-wrapper';

                
                const img = document.createElement('img');
                img.className = 'gallery-image';
                img.src = src;

                
                wrapper.onclick = () => window.open(src, '_blank');

                wrapper.appendChild(img);
                container.appendChild(wrapper);
            });
        } else {
            container.innerHTML = '<p class="text-muted text-center w-100 py-4">Tidak ada foto tambahan.</p>';
        }

        document.getElementById('galleryModal').classList.add('show');
    }

    function closeGalleryModal() {
        document.getElementById('galleryModal').classList.remove('show');
    }
</script>

<?php include("../../Component/bottom.php"); ?>