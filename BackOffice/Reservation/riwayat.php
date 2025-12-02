<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");
include("../Component/pagination.php"); // File fungsi renderPaginator()

// 1. SET VARIABEL UNTUK DIKIRIM KE HELPER
$table_name = "reservasi_arsip";
$base_order_by = " ORDER BY tanggal DESC, jam ASC";

// 2. INCLUDE HELPERNYA
include("../Component/handle_search_pagination.php");
?>

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-archive"></i> Riwayat Arsip</h1>

            <div class="header-buttons" style="display: flex; gap: 10px;">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="action/delete_all_permanent.php" class="btn btn-danger"
                    onclick="return confirm('ANDA YAKIN 100%? Ini akan menghapus SEMUA riwayat arsip secara permanen dan tidak bisa dikembalikan.');">
                    <i class="fas fa-fire"></i> Hapus Semua Permanen
                </a>
            </div>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';

            // Panggil fungsi showNotification() dari global.js
            echo '<script>';
            echo "document.addEventListener('DOMContentLoaded', function() {";
            echo "  showNotification('" . addslashes($_SESSION['message']) . "', '" . $message_type . "');";
            echo "});";
            echo '</script>';

            // Hapus pesan setelah disiapkan
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Arsip (Total: <?= $total_rows ?> data)</h2>

                <form action="riwayat.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchArsip"
                        placeholder="Search..."
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
                            <th>Nama Pelanggan</th>
                            <th>No. WhatsApp</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="arsipTable">
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $tanggalFormat = date('d/m/Y', strtotime($row['tanggal']));
                                $jamFormat = date('H:i', strtotime($row['jam']));
                                $statusClass = match ($row['status']) {
                                    'Confirmed' => 'badge bg-success',
                                    'Cancelled' => 'badge bg-danger',
                                    default => 'badge bg-warning'
                                };
                        ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['no_telepon']) ?></td>
                                    <td><?= $tanggalFormat ?></td>
                                    <td><?= $jamFormat ?></td>
                                    <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                    <td>
                                        <div class="action-buttons" style="display: flex; justify-content: center; gap: 5px;">
                                            <form action="action/delete_permanent.php" method="POST" style="margin:0;" onsubmit="return confirm('ANDA YAKIN? Data ini akan dihapus PERMANEN dan tidak bisa dikembalikan.');"><input type="hidden" name="id_reservasi" value="<?= $row['id_reservasi'] ?>"><button type="submit" class="btn btn-danger btn-sm" title="Hapus Permanen"><i class="fas fa-fire"></i></button></form>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <p>Tidak ada riwayat arsip ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
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

<script src="../assets/js/reservation.js"></script>
<?php include("../Component/bottom.php"); ?>