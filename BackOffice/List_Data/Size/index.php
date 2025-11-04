<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$query = "SELECT id_size, ukuran FROM size ORDER BY id_size DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-ruler-combined"></i>Size</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Size</h2>
                <input type="text" id="searchSize" placeholder="🔍 Cari size...">
            </div>

            <div class="table-responsive">
                <table class="data-table size-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Ukuran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><strong><?= htmlspecialchars($row['ukuran']) ?></strong></td>
                                    <td>
                                        <a href="update.php?id=<?= $row['id_size'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id_size'] ?>)" title="Hapus">
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
                                        <i class="fas fa-inbox"></i>
                                        <p>Belum ada data size. Klik tombol <strong>Tambah</strong> untuk menambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include("../../Component/bottom.php"); ?>