<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php");

$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/si-ukopia/BackOffice/List_Data/Uploads/Alat/";

$sql_kategori = mysqli_query($conn, "SELECT * FROM kategori_alat ORDER BY nama_kategori_alat ASC");

$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';
$kategori_filter = $_GET['kategori_filter'] ?? ''; // Filter Kategori

$base_url_pagin = '?';
$where_conditions = [];
$params = [];
$types = "";

$order_sql = "k.nama_kategori_alat ASC, a.nama_alat ASC";

$base_query = " FROM alat a JOIN kategori_alat k ON a.id_kategori_alat = k.id_kategori_alat ";


if ($search_term != '') {
    $where_conditions[] = "(a.nama_alat LIKE ? OR k.nama_kategori_alat LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= "ss";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

if ($kategori_filter != '') {
    $where_conditions[] = "a.id_kategori_alat = ?";
    $params[] = $kategori_filter;
    $types .= "i";
    $base_url_pagin .= 'kategori_filter=' . urlencode($kategori_filter) . '&';
}

$where_sql = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";

$stmt_count = $conn->prepare("SELECT COUNT(*) as total " . $base_query . $where_sql);
if (!empty($params)) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();

$query_final = "SELECT a.id_alat, a.nama_alat, a.gambar, k.nama_kategori_alat " . $base_query . $where_sql . " ORDER BY " . $order_sql . " LIMIT ? OFFSET ?";
$stmt_data = $conn->prepare($query_final);

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
            <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h2>Total: <?= $total_rows ?> Alat</h2>
                
                <form method="GET" class="search-group" style="display: flex; gap: 8px; align-items: center;">
                    
                    <select name="kategori_filter" class="form-control" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; min-width: 180px;" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        <?php 
                        if(mysqli_num_rows($sql_kategori) > 0) {
                            mysqli_data_seek($sql_kategori, 0);
                            while($kat = mysqli_fetch_assoc($sql_kategori)): 
                        ?>
                            <option value="<?= $kat['id_kategori_alat'] ?>" <?= ($kategori_filter == $kat['id_kategori_alat']) ? 'selected' : '' ?>>
                                <?= $kat['nama_kategori_alat'] ?>
                            </option>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </select>

                    <div style="display: flex;">
                        <input type="text" name="search" placeholder="Search.." value="<?= htmlspecialchars($search_term) ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px 0 0 4px; border-right: none;">
                        <button type="submit" class="btn" style="border-radius: 0 4px 4px 0;"><i class="fas fa-search"></i></button>
                    </div>
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
                            $current_kategori = null;

                            while ($row = $result->fetch_assoc()):
                                $img_url = $BASE_IMAGE_URL . htmlspecialchars($row['gambar']);

                                if ($row['nama_kategori_alat'] != $current_kategori) {
                                    $current_kategori = $row['nama_kategori_alat'];
                                    ?>
                                    <tr style="background-color: #f8f9fa;">
                                        <td colspan="5" style="padding: 10px 15px; font-weight: bold; color: #495057; border-bottom: 2px solid #dee2e6;">
                                            <i class="fas fa-tag" style="margin-right: 5px; color: #adb5bd;"></i>
                                            <?= htmlspecialchars($current_kategori) ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if(!empty($row['gambar'])): ?>
                                        <img src="<?= $img_url ?>" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                                    <?php else: ?>
                                        <span style="color:#adb5bd; font-size: 0.8rem;">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($row['nama_alat']) ?></strong></td>
                                <td><span class="badge" style="background:#e9ecef; color:#495057; padding:4px 8px; border-radius:4px; font-size:0.85em;"><?= htmlspecialchars($row['nama_kategori_alat']) ?></span></td>
                                <td>
                                    <a href="update.php?id=<?= $row['id_alat'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDeleteAlat(<?= $row['id_alat'] ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center" style="padding:20px;">Data tidak ditemukan.</td></tr>
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
