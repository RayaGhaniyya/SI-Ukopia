<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php");

$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/si-ukopia/BackOffice/List_Data/Uploads/Metode/";

// --- LOGIKA PAGINATION & SEARCH ---
$limit = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';
$base_url_pagin = '?';
$where_sql = "";
$params = [];
$types = "";

if ($search_term != '') {
    $where_sql = " WHERE nama_metode LIKE ? ";
    $params[] = "%$search_term%";
    $types .= "s";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

// Count Total
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM metode" . $where_sql);
if (!empty($params)) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();

// Get Data
$query = "SELECT * FROM metode" . $where_sql . " ORDER BY nama_metode ASC LIMIT ? OFFSET ?";
$stmt_data = $conn->prepare($query);
$params[] = $limit; 
$params[] = $offset; 
$types .= "ii";
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$result = $stmt_data->get_result();
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-flask"></i> Data Metode</h1>
            <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>Total: <?= $total_rows ?> Metode</h2>
                <form method="GET" class="search-group">
                    <input type="text" name="search" placeholder="Cari metode..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Icon</th>
                            <th>Nama Metode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): 
                            $no = $offset + 1;
                            while ($row = $result->fetch_assoc()):
                                $img_url = !empty($row['gambar_metode']) ? $BASE_IMAGE_URL . htmlspecialchars($row['gambar_metode']) : '';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if($img_url): ?>
                                        <img src="<?= $img_url ?>" style="width:50px; height:50px; object-fit:contain;">
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($row['nama_metode']) ?></strong></td>
                                <td>
                                    <a href="update.php?id=<?= $row['id_metode'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDeleteMetode(<?= $row['id_metode'] ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-footer"><?php renderPaginator($total_pages, $current_page, $base_url_pagin); ?></div>
        </div>
    </div>
</div>

<script src="../../assets/js/metode.js"></script>
<?php include("../../Component/bottom.php"); ?>