<?php
include("../../../Koneksi/koneksi.php"); // Sesuaikan path
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php"); // 1. INCLUDE PAGINATION

$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/";


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
    $where_conditions[] = "(m.nama_menu LIKE ? OR k.nama_kategori LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss"; 
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}
$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}
$count_query = "
    SELECT COUNT(*) as total 
    FROM menu m 
    JOIN kategori_menu k ON m.id_kategori = k.id_kategori_menu
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
$order_by_sql = " ORDER BY m.nama_menu ASC LIMIT ? OFFSET ?";
$data_query = "
    SELECT 
        m.id_menu, m.nama_menu, m.deskripsi, m.gambar_url, 
        k.nama_kategori 
    FROM menu m 
    JOIN kategori_menu k ON m.id_kategori = k.id_kategori_menu
    $where_sql
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
            <h1><i class="fas fa-utensils"></i> Manajemen Menu</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>
        <?php
        if (isset($_SESSION['message'])) {
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            $alert_class = ($message_type == 'error') ? 'alert alert-danger' : 'alert alert-success';
            echo '<div class="' . $alert_class . '" style="padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo htmlspecialchars($_SESSION['message']);
            echo '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Menu (Total: <?= $total_rows ?> data)</h2>

                <form action="index.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchMenu"
                        placeholder="Search..."
                        value="<?= htmlspecialchars($search_term) ?>">

                    <button type="submit" class="btn" title="Cari">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>


            <div class="table-responsive">
                <table class="data-table" id="menuTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row['id_menu'];
                                $nama_menu = htmlspecialchars($row['nama_menu']);
                                $kategori = htmlspecialchars($row['nama_kategori']);
                                $deskripsi = htmlspecialchars($row['deskripsi']);
                                
                                $gambar_filename = htmlspecialchars($row['gambar_url']);
                                $gambar_dinamis = $BASE_IMAGE_URL . $gambar_filename;

                                $deskripsi_short = strlen($deskripsi) > 60 ? substr($deskripsi, 0, 60) . '...' : $deskripsi;
                        ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td>
                                        <img src="<?php echo $gambar_dinamis; ?>" alt="<?php echo $nama_menu; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['nama_menu']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td><?= $deskripsi_short ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="showDetailMenu(<?= $row['id_menu'] ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="update.php?id=<?= $row['id_menu'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="confirmDelete(<?= $row['id_menu'] ?>)"
                                            title="Hapus">
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
                                        <p>Tidak ada data menu ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        $stmt_data->close();
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
        <div class="popup-header">
            <h2><i class="fas fa-utensils"></i> Detail Menu</h2>
            <button class="popup-close-btn" onclick="closeMenuPopup()">×</button>
        </div>
        <div class="popup-content"></div>
        <div style="margin-top: 20px; text-align: right;">
            <button class="btn btn-cancel" onclick="closeMenuPopup()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>
<script src="../../assets/js/Mobile/menu.js"></script>
<?php include("../../Component/bottom.php"); ?>
