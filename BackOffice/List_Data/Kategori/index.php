<?php
// Path koneksi TIDAK DIUBAH
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php"); // 1. INCLUDE PAGINATION

// --- LOGIKA PAGINATION & SEARCH ---
$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';

$base_url_pagin = '?';
$where_conditions = [];
$params = [];
$types = "";

// 2. SESUAIKAN KOLOM SEARCH
if ($search_term != '') {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(nama_kategori LIKE ?)"; // Cari di 'nama_kategori'
    $params[] = $search_like;
    $types .= "s";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}

// 3. QUERY TOTAL DATA
$count_query = "SELECT COUNT(*) as total FROM kategori" . $where_sql;
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();

// 4. QUERY AMBIL DATA
$order_by_sql = " ORDER BY id_kategori DESC LIMIT ? OFFSET ?"; // Order by asli
$data_query = "SELECT id_kategori, nama_kategori FROM kategori" . $where_sql . $order_by_sql;

$data_params = $params;
$data_params[] = $limit;
$data_params[] = $offset;
$data_types = $types . "ii";

$stmt_data = $conn->prepare($data_query);
$stmt_data->bind_param($data_types, ...$data_params);
$stmt_data->execute();
$result = $stmt_data->get_result();
// --- LOGIKA SELESAI ---
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-tags"></i>Kategori</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Kategori (Total: <?= $total_rows ?>)</h2>

                <form action="index.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchKategori"
                        placeholder="Search..."
                        value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn" title="Cari"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table kategori-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            // 6. UPDATE NOMOR URUT
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                                    <td>
                                        <a href="update.php?id=<?= $row['id_kategori'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id_kategori'] ?>)" title="Hapus">
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
                                <td colspan="3">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <p>Data kategori tidak ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        $stmt_data->close(); // 8. TUTUP STATEMENT
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

<?php include("../../Component/bottom.php"); ?>