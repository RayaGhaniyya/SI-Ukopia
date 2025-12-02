<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/";


$id_menu = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_menu <= 0) {
    echo "<script>alert('ID Menu tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM menu WHERE id_menu = ?");
$stmt->bind_param("i", $id_menu);
$stmt->execute();
$menu = $stmt->get_result()->fetch_assoc();

if (!$menu) {
    echo "<script>alert('Data menu tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();

$kategori_query = "SELECT id_kategori_menu, nama_kategori FROM kategori_menu ORDER BY nama_kategori ASC";
$kategori_result = mysqli_query($conn, $kategori_query);

if (!$kategori_result) {
    die("<div style='background:red;color:white;padding:20px;margin:20px;'>Error Query Kategori: " . mysqli_error($conn) . "</div>");
}
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="form-container">
            <h1><i class="fas fa-edit"></i> Edit Menu</h1>

            <form id="menuUpdateForm" enctype="multipart/form-data">
                <input type="hidden" name="id_menu" value="<?= $id_menu ?>">

                <label>Nama Menu <span style="color:red;">*</span></label>
                <input type="text" name="nama_menu" maxlength="50" required
                    value="<?= htmlspecialchars($menu['nama_menu']) ?>"
                    placeholder="Masukkan nama menu">

                <label>Deskripsi <span style="color:red;">*</span></label>
                <textarea name="deskripsi" rows="4" required
                    placeholder="Masukkan deskripsi menu"><?= htmlspecialchars($menu['deskripsi']) ?></textarea>

                <div class="form-row">
                    <div>
                        <label>Gambar Menu (Opsional)</label>
                        <small style="color:#666; display:block; margin-bottom:8px;">
                            * Klik gambar untuk mengubah. Kosongkan jika tidak ingin mengubah.
                        </small>
                        <input type="file" id="fileInput" name="gambar" accept="image/jpeg,image/jpg,image/png,image/webp"
                            onchange="handleImagePreview(this, 'imagePreview', 'uploadButton')" style="display:none;">

                        <div id="imagePreview" class="image-preview-single"
                            onclick="document.getElementById('fileInput').click()" style="display:flex;">
                            <img src="<?= $BASE_IMAGE_URL . htmlspecialchars($menu['gambar_url']) ?>" alt="Preview">
                            </div>

                        <button type="button" id="uploadButton" class="btn btn-info btn-sm"
                            onclick="document.getElementById('fileInput').click()" style="display:none; margin-top:10px;">
                            <i class="fas fa-upload"></i> Ubah Gambar
                        </button>
                    </div>

                    <div>
                        <label>Kategori <span style="color:red;">*</span></label>
                        <select name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php
                            if ($kategori_result && mysqli_num_rows($kategori_result) > 0) {
                                while ($row = mysqli_fetch_assoc($kategori_result)) {
                                    $selected = ($row['id_kategori_menu'] == $menu['id_kategori']) ? 'selected' : '';
                                    echo "<option value=\"{$row['id_kategori_menu']}\" $selected>" . htmlspecialchars($row['nama_kategori']) . "</option>";
                                }
                            } else {
                                echo "<option value='' disabled style='color:red;'>⚠️ Tidak ada data kategori!</option>";
                            }
                            ?>
                        </select>
                        <?php if (!$kategori_result || mysqli_num_rows($kategori_result) === 0): ?>
                            <small style="color:red; display:block; margin-top:5px;">
                                ⚠️ Data kategori kosong. Hubungi admin untuk menambahkan kategori.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/Mobile/menu.js"></script>
<?php include("../../Component/bottom.php"); ?>
