<?php
// Path Koneksi (Naik 2 Level)
include("../../Koneksi/koneksi.php");
// Path Session (Naik 1 Level)
include("../Component/session.php");

// --- KONFIGURASI PAGINATION & SEARCH ---
$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';
$where_conditions = [];
$params = [];
$types = "";

// Filter Pencarian
if (!empty($search_term)) {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(t.midtrans_order_id LIKE ? OR c.nama LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss";
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}

// 1. Hitung Total Data
$count_query = "SELECT COUNT(*) as total 
                FROM transaksi t 
                JOIN akun_customer c ON t.uid_customer = c.uid 
                $where_sql";
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();

// 2. Ambil Data Transaksi
$data_query = "SELECT t.*, c.nama as nama_customer 
               FROM transaksi t 
               JOIN akun_customer c ON t.uid_customer = c.uid 
               $where_sql 
               ORDER BY t.tanggal_pesan DESC 
               LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($data_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Include Head (Memuat Global CSS & Transaksi CSS)
include("../Component/head.php");
?>

<?php include("../Component/sidebar.php"); ?>

<div class="container">
    <div class="dashboard-header">
        <h1><i class="fas fa-receipt"></i> Riwayat Transaksi</h1>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h2>Data Pesanan Masuk (Total: <?= $total_rows ?>)</h2>
            <form action="index.php" method="GET" class="search-group">
                <input type="text" name="search" placeholder="Cari ID Order / Nama..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit" class="btn"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Order</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = $result->fetch_assoc()):
                            // Badge Status Logic
                            $st = $row['status_pesanan'];
                            $badge = 'badge bg-secondary';
                            if ($st == 'Menunggu Pembayaran') $badge = 'badge bg-warning';
                            elseif ($st == 'Sudah Dibayar') $badge = 'badge bg-info';
                            elseif ($st == 'Diproses') $badge = 'badge bg-primary';
                            elseif ($st == 'Dikirim') $badge = 'badge bg-info';
                            elseif ($st == 'Selesai') $badge = 'badge bg-success';
                            elseif ($st == 'Batal' || $st == 'Kadaluarsa') $badge = 'badge bg-danger';
                            elseif ($st == 'Pengajuan Batal') $badge = 'badge bg-danger';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong>#<?= $row['id_transaksi'] ?></strong><br>
                                    <small style="color:#888;"><?= $row['midtrans_order_id'] ?></small>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                                <td><?= htmlspecialchars($row['nama_customer']) ?></td>
                                <td>Rp <?= number_format($row['total_pembayaran'], 0, ',', '.') ?></td>
                                <td><span class="badge <?= $badge ?>"><?= $st ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white" onclick="lihatDetail(<?= $row['id_transaksi'] ?>)">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <p>Belum ada data transaksi.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer" style="padding-top: 10px;">
            <?php
            include("../Component/pagination.php");
            renderPaginator($total_pages, $current_page, '?search=' . urlencode($search_term) . '&');
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalBodyContent">
            </div>
        </div>
    </div>
</div>

<script src="/SI-Ukopia/BackOffice/assets/js/transaksi.js"></script>

<?php include("../Component/bottom.php"); ?>