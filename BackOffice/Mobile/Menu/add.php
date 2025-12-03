<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");


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
            <h1><i class="fas fa-plus-circle"></i> Tambah Menu</h1>

            <form id="menuAddForm" enctype="multipart/form-data">
                <label>Nama Menu <span style="color:red;">*</span></label>
                <input type="text" name="nama_menu" maxlength="50" required placeholder="Masukkan nama menu">

                <label>Deskripsi <span style="color:red;">*</span></label>
                <textarea name="deskripsi" rows="4" required placeholder="Masukkan deskripsi menu"></textarea>

                <div class="form-row">
                    <div>
                        <label>Gambar Menu <span style="color:red;">*</span></label>
                        <small style="color:#666; display:block; margin-bottom:8px;">
                            * Format: JPG, JPEG, PNG, WEBP. Max 5MB.
                        </small>
                        <input type="file" id="fileInput" name="gambar" accept="image/jpeg,image/jpg,image/png,image/webp"
                            onchange="handleImagePreview(this, 'imagePreview', 'uploadButton')" style="display:none;" required>

                        <div id="imagePreview" class="image-preview-single" onclick="document.getElementById('fileInput').click()">
                            <span>Klik untuk memilih gambar</span>
                        </div>

                        <button type="button" id="uploadButton" class="btn btn-info btn-sm"
                            onclick="document.getElementById('fileInput').click()" style="margin-top:10px; width:200px; max-width:100%;">
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
                                    echo "<option value=\"{$row['id_kategori_menu']}\">" . htmlspecialchars($row['nama_kategori']) . "</option>";
                                }
                            }
                            ?>
                        </select>
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