<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
include("../../Component/pagination.php");

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

// JOIN ke akun_customer untuk ambil nama pembuat
$base_query = " FROM resep r JOIN akun_customer c ON r.uid_akun = c.uid ";

if ($search_term != '') {
    $where_conditions[] = "(r.nama_resep LIKE ? OR c.nama LIKE ?)";
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
$stmt_data = $conn->prepare("SELECT r.*, c.nama as nama_pembuat " . $base_query . $where_sql . " ORDER BY r.tanggal DESC LIMIT ? OFFSET ?");
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
            <h1><i class="fas fa-book"></i> Data Resep</h1>
            <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Resep</a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>Total: <?= $total_rows ?> Resep</h2>
                <form method="GET" class="search-group">
                    <input type="text" name="search" placeholder="Cari resep / pembuat..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Resep</th>
                            <th>Pembuat</th>
                            <th>Metode Seduh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): 
                            $no = $offset + 1;
                            while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_resep']) ?></strong></td>
                                <td><?= htmlspecialchars($row['nama_pembuat']) ?></td>
                                <td>
                                    <small>
                                        Kopi: <?= $row['jumlah_kopi'] ?>g | Air: <?= $row['jumlah_air'] ?>ml<br>
                                        Suhu: <?= $row['suhu'] ?>°C | Waktu: <?= $row['waktu_ekstraksi'] ?>s
                                    </small>
                                </td>
                                <td>
                                    <a href="update.php?id=<?= $row['id_resep'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDeleteResep(<?= $row['id_resep'] ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6" class="text-center">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-footer"><?php renderPaginator($total_pages, $current_page, $base_url_pagin); ?></div>
        </div>
    </div>
</div>

<script src="../../assets/js/Mobile/resep.js"></script>
<?php include("../../Component/bottom.php"); ?>