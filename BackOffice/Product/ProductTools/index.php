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

$where_conditions = ["p.id_kategori IN (4, 6)"];

if ($search_term != '') {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(p.nama_produk LIKE ? OR p.link LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss"; // 2 string
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

$where_sql = " WHERE " . implode(" AND ", $where_conditions);

$count_query = "
    SELECT COUNT(*) as total 
    FROM produk p
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


$order_by_sql = " ORDER BY p.id_produk DESC LIMIT ? OFFSET ?";

$data_query = "
    SELECT 
        p.id_produk, p.nama_produk, p.gambar_url, p.deskripsi, p.link,
        k.nama_kategori 
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id_kategori
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
            <h1><i class="fas fa-tools"></i> Manajemen Alat & Rekomendasi</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            echo '<script>';
            echo "document.addEventListener('DOMContentLoaded', function() {";
            echo "  showNotification('" . addslashes($_SESSION['message']) . "', '" . $message_type . "');";
            echo "});";
            echo '</script>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Alat & Rekomendasi (Total: <?= $total_rows ?> data)</h2>

                <form action="index.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchProduk"
                        placeholder="Search..."
                        value="<?= htmlspecialchars($search_term) ?>">

                    <button type="submit" class="btn" title="Cari">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table" id="produkTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Link Eksternal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $nama_produk = htmlspecialchars($row['nama_produk']);
                                $gambar_url = htmlspecialchars($row['gambar_url']);

                                $link_short = strlen($row['link']) > 50
                                    ? substr(htmlspecialchars($row['link']), 0, 50) . '...'
                                    : htmlspecialchars($row['link']);

                                $gambar_dinamis = str_replace("localhost", $current_host, $gambar_url);
                        ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <img src="<?php echo $gambar_dinamis; ?>" alt="<?php echo $nama_produk; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><strong><?= $nama_produk ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank">
                                            <?= $link_short ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="display: flex; justify-content: center; gap: 5px;">
                                            <a href="update.php?id=<?= $row['id_produk'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="action/delete.php?id=<?= $row['id_produk'] ?>"
                                                class="btn btn-danger btn-sm"
                                                title="Hapus"
                                                onclick="return confirm('Yakin ingin menghapus produk ini?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <p>Tidak ada data alat ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
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

<?php include("../../Component/bottom.php"); ?>
