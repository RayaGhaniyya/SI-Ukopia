<?php
// (File: koneksi.php) diasumsikan sudah membuat variabel $conn
include("../../../Koneksi/koneksi.php"); 
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-mobile-screen"></i> Manajemen Menu Mobile</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Menu
            </a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Menu Mobile</h2>
                <input type="text" id="searchMenu" placeholder="🔍 Cari menu...">
            </div>

           <div class="table-responsive">
                <table class="gallery-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Gambar</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="galleryTableBody">
                        <?php
                        // --- PERUBAHAN DIMULAI DI SINI ---

                        // 1. Query diubah untuk mengambil data dari tabel 'menu' dan 'kategori_menu'
                        $query = "
                            SELECT 
                                m.id_menu, 
                                m.nama_menu, 
                                m.deskripsi, 
                                m.gambar_url, 
                                k.nama_kategori 
                            FROM 
                                menu m 
                            JOIN 
                                kategori_menu k ON m.id_kategori = k.id_kategori_menu
                            ORDER BY 
                                m.nama_menu ASC
                        ";

                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row['id_menu'];
                                $nama_menu = htmlspecialchars($row['nama_menu']);
                                $kategori = htmlspecialchars($row['nama_kategori']);
                                $deskripsi = htmlspecialchars($row['deskripsi']);
                                $gambar_url = htmlspecialchars($row['gambar_url']);
                                
                                $deskripsi_short = strlen($deskripsi) > 60 ? substr($deskripsi, 0, 60) . '...' : $deskripsi;
                        ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><strong><?php echo $nama_menu; ?></strong></td>
                                    <td><?php echo $kategori; ?></td>
                                    <td>
                                        <img src="<?php echo $gambar_url; ?>" alt="<?php echo $nama_menu; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><?php echo $deskripsi_short; ?></td>
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
                                        <p>Belum ada data menu. Klik tombol <strong>Tambah Menu</strong> untuk menambahkan.</p>
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

<?php include("../../Component/bottom.php"); ?>