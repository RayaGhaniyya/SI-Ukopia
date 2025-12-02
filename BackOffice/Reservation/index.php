<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");
include("../Component/pagination.php"); // File fungsi renderPaginator()

$table_name = "reservasi";
$base_order_by = " ORDER BY 
    CASE status
        WHEN 'Pending' THEN 1
        WHEN 'Confirmed' THEN 2
        WHEN 'Cancelled' THEN 3
        ELSE 4
    END, 
    tanggal DESC, 
    jam ASC";

include("../Component/handle_search_pagination.php");
?>

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-calendar-check"></i> Manajemen Reservasi</h1>
            <a href="riwayat.php" class="btn btn-primary">
                <i class="fas fa-archive"></i> Riwayat Arsip
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
                <h2><i class="fas fa-list"></i> Data Reservasi (Total: <?= $total_rows ?> data)</h2>

                <form action="index.php" method="GET" class="search-group">
                    <input
                        type="text"
                        name="search"
                        id="searchTable"
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
                    <tbody id="reservationTable">
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

                                $nomor_display = htmlspecialchars($row['no_telepon']);
                                $nomor_link = preg_replace('/[^0-9]/', '', $row['no_telepon']);

                                if (substr($nomor_link, 0, 1) === '0') {
                                    $nomor_link = '62' . substr($nomor_link, 1);
                                } elseif (substr($nomor_link, 0, 1) === '8') {
                                    $nomor_link = '62' . $nomor_link;
                                }
                        ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                                    <td><a href="https://wa.me/<?= $nomor_link ?>" target="_blank"><?= $nomor_display ?></a></td>
                                    <td><?= $tanggalFormat ?></td>
                                    <td><?= $jamFormat ?></td>
                                    <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                    <td>
                                        <div class="action-buttons" style="display: flex; justify-content: center; gap: 5px;">
                                            <?php if ($row['status'] == 'Pending') : ?>
                                                <form action="action/update_status.php" method="POST" style="margin:0;"><input type="hidden" name="id_reservasi" value="<?= $row['id_reservasi'] ?>"><input type="hidden" name="status" value="Confirmed"><button type="submit" class="btn btn-success btn-sm" title="Konfirmasi Reservasi"><i class="fas fa-check"></i></button></form>
                                                <form action="action/update_status.php" method="POST" style="margin:0;"><input type="hidden" name="id_reservasi" value="<?= $row['id_reservasi'] ?>"><input type="hidden" name="status" value="Cancelled"><button type="submit" class="btn btn-warning btn-sm" title="Batalkan Reservasi"><i class="fas fa-times"></i></button></form>
                                            <?php elseif ($row['status'] == 'Confirmed') : ?>
                                                <form action="action/update_status.php" method="POST" style="margin:0;"><input type="hidden" name="id_reservasi" value="<?= $row['id_reservasi'] ?>"><input type="hidden" name="status" value="Cancelled"><button type="submit" class="btn btn-warning btn-sm" title="Batalkan Reservasi"><i class="fas fa-times"></i></button></form>
                                            <?php endif; ?>
                                            <a href="action/delete.php?id=<?= $row['id_reservasi'] ?>"
                                                class="btn btn-danger btn-sm"
                                                title="Arsipkan"
                                                onclick="return confirm('Yakin ingin meng-arsip reservasi ini?');">
                                                <i class="fas fa-archive"></i>
                                            </a>
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
                                        <p>Tidak ada data reservasi ditemukan<?php if ($search_term != '') echo " untuk pencarian '<b>" . htmlspecialchars($search_term) . "</b>'"; ?>.</p>
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
