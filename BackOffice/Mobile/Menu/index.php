<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
$current_host = $_SERVER['HTTP_HOST'];
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

        <?php
        if (isset($_SESSION['message'])) {
            // Tentukan kelas CSS berdasarkan tipe pesan
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            $alert_class = ($message_type == 'error') ? 'alert alert-danger' : 'alert alert-success';

            echo '<div class="' . $alert_class . '" style="padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo htmlspecialchars($_SESSION['message']);
            echo '</div>';

            // Hapus pesan setelah ditampilkan
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Data Menu Mobile</h2>
                <input type="text" id="searchMenu" placeholder="🔍 Cari menu...">
            </div>

            <div class="table-responsive">
                <table class="data-table">
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
                    <tbody id="menuTableBody">
                        <?php
                        $query = "
                            SELECT 
                                m.id_menu, m.nama_menu, m.deskripsi, m.gambar_url, 
                                k.nama_kategori 
                            FROM menu m 
                            JOIN kategori_menu k ON m.id_kategori = k.id_kategori_menu
                            ORDER BY m.nama_menu ASC
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
                                $gambar_dinamis = str_replace("localhost", $current_host, $gambar_url);
                        ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><strong><?php echo $nama_menu; ?></strong></td>
                                    <td><?php echo $kategori; ?></td>
                                    <td>
                                        <img src="<?php echo $gambar_dinamis; ?>" alt="<?php echo $nama_menu; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><?php echo $deskripsi_short; ?></td>
                                    <td>
                                        <button class="btn btn-info" onclick="showDetailMenu(<?php echo $id; ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="update.php?id=<?php echo $id; ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger" onclick="confirmDeleteMenu(<?php echo $id; ?>)" title="Hapus">
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

<script src="../../assets/js/Mobile/menu.js"></script>
<?php include("../../Component/bottom.php"); ?>