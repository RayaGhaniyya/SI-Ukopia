<?php
// [UBAH] Path koneksi sesuai lokasi folder
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

// [UBAH] Query - ganti nama tabel dan kolom sesuai struktur DB
$query = "SELECT id_grind, nama_grind FROM grind_size ORDER BY id_grind DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <!-- [UBAH] Icon dan title sesuai modul -->
            <h1><i class="fas fa-grip-horizontal"></i>Grind Size</h1>
            <!-- [UBAH] Link ke halaman add -->
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <!-- [UBAH] Title dan ID search input -->
                <h2><i class="fas fa-list"></i> Data Grind Size</h2>
                <input type="text" id="searchGrindSize" placeholder="🔍 Cari grind size...">
            </div>

            <div class="table-responsive">
                <!-- [UBAH] Class table untuk JS targeting -->
                <table class="data-table grind-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <!-- [UBAH] Header kolom sesuai data -->
                            <th>Grind Size</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                // [UBAH] Escape output sesuai field
                        ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_grind']) ?></strong></td>
                                    <td>
                                        <!-- [UBAH] Parameter ID dan link update -->
                                        <a href="update.php?id=<?= $row['id_grind'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- [UBAH] Parameter ID untuk delete -->
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id_grind'] ?>)" title="Hapus">
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
                                <!-- [UBAH] Colspan sesuai jumlah kolom -->
                                <td colspan="3">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <!-- [UBAH] Text empty state -->
                                        <p>Belum ada data grind size. Klik tombol <strong>Tambah</strong> untuk menambahkan.</p>
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

<!-- [UBAH] Script JS sesuai nama modul -->
<?php include("../../Component/bottom.php"); ?>