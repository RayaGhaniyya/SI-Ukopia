<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");
include("../Component/pagination.php");

$limit = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? '';
$base_url_pagin = '?';
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search_term)) {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(t.midtrans_order_id LIKE ? OR c.nama LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}

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

$data_query = "SELECT t.*, c.nama as nama_customer 
               FROM transaksi t 
               JOIN akun_customer c ON t.uid_customer = c.uid 
               $where_sql 
               ORDER BY t.tanggal_pesan DESC 
               LIMIT ? OFFSET ?";

$data_params = $params;
$data_params[] = $limit;
$data_params[] = $offset;
$data_types = $types . "ii";

$stmt = $conn->prepare($data_query);
$stmt->bind_param($data_types, ...$data_params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-receipt"></i> Riwayat Transaksi</h1>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Pesanan Masuk (Total: <?= $total_rows ?> data)</h2>

                <form action="index.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchTransaction"
                        placeholder="Cari ID Order / Nama..."
                        value="<?= htmlspecialchars($search_term) ?>">

                    <button type="submit" class="btn" title="Cari">
                        <i class="fas fa-search"></i>
                    </button>
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
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $no = $offset + 1;
                            while ($row = $result->fetch_assoc()) {
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
                                    <td><?= $no ?></td>
                                    <td>
                                        <strong>#<?= $row['id_transaksi'] ?></strong><br>
                                        <small style="color:#888;"><?= $row['midtrans_order_id'] ?></small>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                                    <td><?= htmlspecialchars($row['nama_customer']) ?></td>
                                    <td>Rp <?= number_format($row['total_pembayaran'], 0, ',', '.') ?></td>
                                    <td><span class="<?= $badge ?>"><?= $st ?></span></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id_transaksi'] ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php
                                $no++;
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <p>Tidak ada data transaksi ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        $stmt->close();
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
