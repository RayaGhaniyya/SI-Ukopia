<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");

// Query data gallery
$query = "
    SELECT 
        g.id_galery, 
        g.judul, 
        g.deskripsi, 
        g.tanggal,
        COUNT(d.id_detail_galery) as total_foto
    FROM galery g
    LEFT JOIN detail_galery d ON g.id_galery = d.id_galery
    GROUP BY g.id_galery, g.judul, g.deskripsi, g.tanggal
    ORDER BY g.id_galery DESC
";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-images"></i> Manajemen Galeri</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Galeri</h2>
                <input type="text" id="searchGallery" placeholder="🔍 Cari galeri...">
            </div>

            <div class="table-responsive">
                <table class="data-table gallery-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                            <th>Jumlah Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $tanggalFormat = date('d/m/Y', strtotime($row['tanggal']));
                                $deskripsiShort = strlen($row['deskripsi']) > 60
                                    ? substr(htmlspecialchars($row['deskripsi']), 0, 60) . '...'
                                    : htmlspecialchars($row['deskripsi']);
                        ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
                                    <td><?= $deskripsiShort ?></td>
                                    <td><?= $tanggalFormat ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-image"></i> <?= $row['total_foto'] ?> foto
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="showDetail(<?= $row['id_galery'] ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="update.php?id=<?= $row['id_galery'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id_galery'] ?>)" title="Hapus">
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
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>Belum ada data galeri. Klik tombol <strong>Tambah</strong> untuk menambahkan.</p>
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

<!-- POPUP DETAIL GAMBAR -->
<div id="detailPopup" class="popup-overlay">
    <div class="popup-box">
        <h2><i class="fas fa-images"></i> Detail Gambar Galeri</h2>
        <div id="detailImages" class="image-grid"></div>
        <div style="margin-top: 20px;">
            <button class="btn btn-cancel" onclick="closeDetailPopup()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>

<script src="../assets/js/gallery.js"></script>
<?php include("../Component/bottom.php"); ?>