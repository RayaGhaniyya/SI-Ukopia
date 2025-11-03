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
            <h1><i class="fas fa-utensils"></i> Manajemen Menu</h1>
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
                <h2><i class="fas fa-list"></i> Data Menu</h2>
                <input type="text" id="searchMenu" placeholder="🔍 Cari menu...">
            </div>

            <div class="table-responsive">
                <table class="data-table" id="menuTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                    <td><?= $no ?></td>
                                    <td>
                                        <img src="<?php echo $gambar_dinamis; ?>" alt="<?php echo $nama_menu; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['nama_menu']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td><?= $deskripsi_short ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="showDetailMenu(<?= $row['id_menu'] ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="update.php?id=<?= $row['id_menu'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteMenu(<?= $row['id_menu'] ?>)" title="Hapus">
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
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- POPUP DETAIL MENU -->
<div id="detailPopup" class="popup-overlay">
    <div class="popup-box">
        <div class="popup-header">
            <h2><i class="fas fa-utensils"></i> Detail Menu</h2>
            <button class="popup-close-btn" onclick="closeMenuPopup()">×</button>
        </div>
        <div class="popup-content"></div>
        <div style="margin-top: 20px; text-align: right;">
            <button class="btn btn-cancel" onclick="closeMenuPopup()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>
</div>

<?php include("../../Component/bottom.php"); ?>