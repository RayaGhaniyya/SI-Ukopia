<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");
?>


<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-images"></i> Manajemen Galeri</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Galeri
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Galeri</h2>
                <input type="text" id="searchGallery" placeholder="🔍 Cari galeri...">
            </div>

            <div class="table-responsive">
                <table class="gallery-table">
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
                    <tbody id="galleryTableBody">
                        <?php
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

                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row['id_galery'];
                                $judul = htmlspecialchars($row['judul']);
                                $deskripsi = htmlspecialchars($row['deskripsi']);
                                $deskripsi_short = strlen($deskripsi) > 60 ? substr($deskripsi, 0, 60) . '...' : $deskripsi;
                                $tanggal = $row['tanggal'];
                                $totalFoto = $row['total_foto'];

                                // Format tanggal ke dd/mm/yyyy
                                $tanggal_obj = DateTime::createFromFormat('Y-m-d', $tanggal);
                                $tanggalFormat = $tanggal_obj ? $tanggal_obj->format('d/m/Y') : $tanggal;
                        ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><strong><?php echo $judul; ?></strong></td>
                                    <td><?php echo $deskripsi_short; ?></td>
                                    <td><?php echo $tanggalFormat; ?></td>
                                    <td>
                                        <span style="background:#e0f2fe; color:#0369a1; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:500;">
                                            <i class="fas fa-image"></i> <?php echo $totalFoto; ?> foto
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info" onclick="showDetail(<?php echo $id; ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="update.php?id=<?php echo $id; ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger" onclick="confirmDelete(<?php echo $id; ?>)" title="Hapus">
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
                                        <p>Belum ada data galeri. Klik tombol <strong>Tambah Galeri</strong> untuk menambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- POPUP DETAIL GAMBAR -->
<div id="detailPopup" class="popup-overlay" style="display:none;">
    <div class="popup-box light">
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