<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");
?>

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="form-container light">
        <h1><i class="fas fa-plus-circle"></i> Tambah Galeri</h1>

        <form id="galleryAddForm" enctype="multipart/form-data">
            <label>Judul Galeri <span style="color:red;">*</span></label>
            <input type="text" name="judul" maxlength="50" required placeholder="Masukkan judul galeri">

            <label>Deskripsi <span style="color:red;">*</span></label>
            <textarea name="deskripsi" rows="4" required placeholder="Masukkan deskripsi galeri"></textarea>

            <label>Tanggal <span style="color:red;">*</span></label>
            <input type="date" name="tanggal" id="tanggal" required>

            <label>Upload Gambar <span style="color:red;">*</span></label>
            <small style="color:#666; display:block; margin-bottom:8px;">
                * Maksimal 4 gambar, format: JPG, JPEG, PNG, GIF, WEBP. Max 5MB per file
            </small>

            <!-- Hidden file input -->
            <input type="file"
                id="fileInput"
                name="gambar[]"
                multiple
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                onchange="addMoreImages(this)"
                style="display: none;">

            <!-- Custom button untuk trigger file input -->
            <button type="button"
                class="btn btn-info"
                onclick="document.getElementById('fileInput').click()"
                style="margin-bottom: 10px;">
                <i class="fas fa-plus"></i> Pilih Gambar (Max 4)
            </button>

            <!-- Preview container -->
            <div id="previewContainer" class="image-preview-grid"></div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Galeri
                </button>
                <a href="index.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include("../Component/bottom.php"); ?>