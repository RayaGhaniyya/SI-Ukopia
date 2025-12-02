<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$kategori_query = "SELECT * FROM kategori WHERE id_kategori IN (4, 6)";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-plus"></i> Tambah Alat / Rekomendasi</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form class="form-container" action="action/store.php" method="POST" enctype="multipart/form-data" id="addToolForm">

            <h3>Informasi Utama Produk</h3>

            <div class="form-row">
                <div>
                    <label for="nama_produk">Nama Produk (cth: Hario V60)</label>
                    <input type="text" id="nama_produk" name="nama_produk" required>
                </div>
                <div>
                    <label for="id_kategori">Kategori Produk</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)): ?>
                            <option value="<?= $kategori['id_kategori'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <label>Gambar Utama Produk <span style="color:red;">*</span></label>
            <small style="color:#666; display:block; margin-bottom:8px;">
                * Format: JPG, JPEG, PNG, WEBP. Max 5MB.
            </small>
            <input type="file" id="fileInputTools" name="gambar_url" accept="image/jpeg,image/jpg,image/png,image/webp"
                onchange="handleImagePreview(this, 'imagePreviewTools', 'uploadButtonTools')" style="display:none;" required>

            <div id="imagePreviewTools" class="image-preview-single"
                onclick="document.getElementById('fileInputTools').click()"
                style="width: 200px; height: 200px;">
                <span>Klik untuk memilih gambar</span>
            </div>

            <button type="button" id="uploadButtonTools" class="btn btn-info btn-sm"
                onclick="document.getElementById('fileInputTools').click()" style="margin-top:10px; width:200px; max-width:100%;">
                <i class="fas fa-plus"></i> Pilih Gambar
            </button>
            <label for="link">Link Eksternal (Tokopedia/Shopee/dll)</label>
            <input type="text" id="link" name="link" placeholder="https://..." required>

            <label for="deskripsi">Deskripsi Singkat Produk</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>

            <input type="hidden" name="origin" value="">
            <input type="hidden" name="altitude" value="">
            <input type="hidden" name="variety" value="">
            <input type="hidden" name="process" value="">
            <input type="hidden" name="notes" value="">
            <input type="hidden" name="varian_size[]" value="">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>

    </div>
</div>
<?php include("../../Component/bottom.php"); ?>
