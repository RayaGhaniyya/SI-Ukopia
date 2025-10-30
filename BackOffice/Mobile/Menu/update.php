<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message_type'] = 'error';
    $_SESSION['message'] = 'ID Menu tidak valid.';
    header("Location: index.php");
    exit;
}

$id_menu = (int)$_GET['id'];

// Ambil data menu yang ada dari DB
$sql = "SELECT * FROM menu WHERE id_menu = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_menu);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message_type'] = 'error';
    $_SESSION['message'] = 'Data menu tidak ditemukan.';
    header("Location: index.php");
    exit;
}
$menu = $result->fetch_assoc();
$stmt->close();

// Ambil data kategori
$kategori_query = "SELECT id_kategori_menu, nama_kategori FROM kategori_menu ORDER BY nama_kategori ASC";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<link rel="stylesheet" href="../../assets/css/Mobile/menu.css">
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="form-container light">
        <h1><i class="fas fa-edit"></i> Edit Menu</h1>

        <form id="menuEditForm" action="process_update.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id_menu" value="<?php echo $menu['id_menu']; ?>">
            <input type="hidden" name="gambar_url_lama" value="<?php echo htmlspecialchars($menu['gambar_url']); ?>">

            <label>Nama Menu <span style="color:red;">*</span></label>
            <input type="text" name="nama_menu" maxlength="50" required placeholder="Masukkan nama menu" value="<?php echo htmlspecialchars($menu['nama_menu']); ?>">

            <label>Deskripsi <span style="color:red;">*</span></label>
            <textarea name="deskripsi" rows="4" required placeholder="Masukkan deskripsi menu"><?php echo htmlspecialchars($menu['deskripsi']); ?></textarea>

            <div class="form-row">
                <div>
                    <label>Gambar Menu</label>
                    <small style="color:#666; display:block; margin-bottom:8px; text-align: center;">
                        * Kosongkan jika tidak ingin mengubah gambar.
                    </small>
                    <input type="file" id="fileInput" name="gambar" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="handleImagePreview(this)" style="display: none;">
                    
                    <div id="imagePreview" onclick="triggerFileInput()" style="display: flex;">
                        <img src="<?php echo htmlspecialchars($menu['gambar_url']); ?>" alt="Preview">
                    </div>
                    <button type="button" id="uploadButton" class="btn btn-info" onclick="triggerFileInput()" style="display:none;">
                        <i class="fas fa-plus"></i> Pilih Gambar
                    </button>
                </div>
                <div>
                    <label>Kategori <span style="color:red;">*</span></label>
                    <select name="id_kategori" required>
                        <option value="">Pilih Kategori</option>
                        <?php
                        if ($kategori_result && mysqli_num_rows($kategori_result) > 0) {
                            while ($row = mysqli_fetch_assoc($kategori_result)) {
                                $id_kategori = $row['id_kategori_menu'];
                                $nama_kategori = htmlspecialchars($row['nama_kategori']);
                                // Tambahkan 'selected' jika ID-nya cocok
                                $selected = ($id_kategori == $menu['id_kategori']) ? 'selected' : '';
                                echo "<option value=\"$id_kategori\" $selected>$nama_kategori</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <a href="index.php" class="btn btn-cancel"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="../../assets/js/Mobile/menu.js"></script>


<?php include("../../Component/bottom.php"); ?>