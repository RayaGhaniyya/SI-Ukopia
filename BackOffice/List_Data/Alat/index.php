<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php");

// URL Gambar
$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/si-ukopia/BackOffice/List_Data/Uploads/Alat/";

// --- LOGIKA DATA ---
$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';
$base_url_pagin = '?';
$where_conditions = [];
$params = [];
$types = "";

// [FIX] Gunakan id_kategori_alat (sesuai database) bukan id_kategori
$base_query = " FROM alat a JOIN kategori_alat k ON a.id_kategori_alat = k.id_kategori_alat ";

if ($search_term != '') {
    $where_conditions[] = "(a.nama_alat LIKE ? OR k.nama_kategori_alat LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= "ss";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

$where_sql = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";

// Count Total
$stmt_count = $conn->prepare("SELECT COUNT(*) as total " . $base_query . $where_sql);
if (!empty($params)) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();

// Get Data
$stmt_data = $conn->prepare("SELECT a.id_alat, a.nama_alat, a.gambar, k.nama_kategori_alat " . $base_query . $where_sql . " ORDER BY a.nama_alat ASC LIMIT ? OFFSET ?");
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
            <h1><i class="fas fa-tools"></i> Data Alat</h1>
            <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>Total: <?= $total_rows ?> Alat</h2>
                <form method="GET" class="search-group">
                    <input type="text" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): 
                            $no = $offset + 1;
                            while ($row = $result->fetch_assoc()):
                                $img_url = $BASE_IMAGE_URL . htmlspecialchars($row['gambar']);
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><img src="<?= $img_url ?>" style="width:70px;height:70px;object-fit:cover;border-radius:6px;"></td>
                                <td><strong><?= htmlspecialchars($row['nama_alat']) ?></strong></td>
                                <td><?= htmlspecialchars($row['nama_kategori_alat']) ?></td>
                                <td>
                                    <a href="update.php?id=<?= $row['id_alat'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDeleteAlat(<?= $row['id_alat'] ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-footer"><?php renderPaginator($total_pages, $current_page, $base_url_pagin); ?></div>
        </div>
    </div>
</div>

<script src="../../assets/js/alat.js"></script>
<?php include("../../Component/bottom.php"); ?>