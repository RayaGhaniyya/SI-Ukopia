<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");
include("../Component/pagination.php"); // 1. INCLUDE PAGINATION
$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;
$search_term = $_GET['search'] ?? '';
$base_url_pagin = '?';
$where_conditions = [];
$params = [];
$types = "";
if ($search_term != '') {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(g.judul LIKE ? OR g.deskripsi LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss"; // 2 string
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}
$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}
$count_query = "
    SELECT COUNT(*) as total 
    FROM galery g
    $where_sql 
";
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();
$order_by_sql = " ORDER BY g.id_galery DESC LIMIT ? OFFSET ?"; // Urutan asli kamu
$data_query = "
    SELECT 
        g.id_galery, 
        g.judul, 
        g.deskripsi, 
        g.tanggal,
        COUNT(d.id_detail_galery) as total_foto
    FROM galery g
    LEFT JOIN detail_galery d ON g.id_galery = d.id_galery
    $where_sql
    GROUP BY g.id_galery, g.judul, g.deskripsi, g.tanggal
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
    <?php include("../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-images"></i> Manajemen Galeri</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>
        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Galeri (Total: <?= $total_rows ?> data)</h2>
                <form action="index.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchGallery"
                        placeholder="Search..."
                        value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn" title="Cari">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="data-table gallery-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                            <th>Jumlah Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $tanggalFormat = date('d/m/Y', strtotime($row['tanggal']));
                                $deskripsiShort = strlen($row['deskripsi']) > 60
                                    ? substr(htmlspecialchars($row['deskripsi']), 0, 60) . '...'
                                    : htmlspecialchars($row['deskripsi']);
                        ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
                                    <td><?= $deskripsiShort ?></td>
                                    <td><?= $tanggalFormat ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-image"></i> <?= $row['total_foto'] ?> foto
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="showDetail(<?= $row['id_galery'] ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="update.php?id=<?= $row['id_galery'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id_galery'] ?>)" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php
                                $no++;
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <p>Tidak ada data galeri ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        $stmt_data->close(); // 10. TUTUP STATEMENT
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="table-footer" style="padding-top: 10px;">
                <?php
                renderPaginator($total_pages, $current_page, $base_url_pagin);
                ?>
            </div>
        </div>
    </div>
</div>
<div id="detailPopup" class="popup-overlay">
    <div class="popup-box">
        <h2><i class="fas fa-images"></i> Detail Gambar Galeri</h2>
        <div id="detailImages" class="image-grid"></div>
        <div style="margin-top: 20px;">
            <button class="btn btn-cancel" onclick="closeDetailPopup()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>
<script src="../assets/js/gallery.js"></script>
<?php include("../Component/bottom.php"); ?>

