<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
$size_query = "SELECT * FROM size ORDER BY ukuran ASC";
$size_result = mysqli_query($conn, $size_query);
$size_options = mysqli_fetch_all($size_result, MYSQLI_ASSOC);
$id_kategori_merch = 3;
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-plus"></i> Tambah Merchandise</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <form class="form-container" action="action/store.php" method="POST" enctype="multipart/form-data" id="addMerchForm">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori_merch ?>">
            <h3>Informasi Utama Produk</h3>
            <div class="form-row">
                <div>
                    <label for="nama_produk">Nama Produk (cth: T-Shirt Ukopia)</label>
                    <input type="text" id="nama_produk" name="nama_produk" required>
                </div>
                <div>
                    <label>Gambar Utama Produk <span style="color:red;">*</span></label>
                    <small style="color:#666; display:block; margin-bottom:8px;">
                        * Gambar yang muncul di halaman depan (Thumbnail).
                    </small>
                    <input type="file" id="fileInputMerch" name="gambar_url" accept="image/jpeg,image/jpg,image/png,image/webp"
                        onchange="handleImagePreview(this, 'imagePreviewMerch', 'uploadButtonMerch')" style="display:none;" required>
                    <div id="imagePreviewMerch" class="image-preview-single"
                        onclick="document.getElementById('fileInputMerch').click()"
                        style="width: 200px; height: 200px;">
                        <span>Klik untuk memilih gambar</span>
                    </div>
                    <button type="button" id="uploadButtonMerch" class="btn btn-info btn-sm"
                        onclick="document.getElementById('fileInputMerch').click()" style="margin-top:10px; width:200px; max-width:100%;">
                        <i class="fas fa-plus"></i> Pilih Gambar
                    </button>
                </div>
            </div>
            <div style="margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px dashed #ccc;">
                <label style="font-weight: bold;">Galeri Foto Tambahan (Opsional)</label>
                <small style="color:#666; display:block; margin-bottom:10px;">
                    * Anda bisa memilih banyak foto sekaligus (Tahan tombol CTRL saat memilih).
                </small>
                <input type="file" name="galeri[]" multiple accept="image/*" class="form-control">
            </div>
            <label for="deskripsi" style="margin-top: 20px;">Deskripsi Lengkap Produk</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>
            <input type="hidden" name="origin" value="">
            <input type="hidden" name="altitude" value="">
            <input type="hidden" name="variety" value="">
            <input type="hidden" name="process" value="">
            <input type="hidden" name="notes" value="">
            <input type="hidden" name="link" value="">
            <div class="variant-section">
                <div class="variant-header">
                    <h3>Varian (Size, Harga & Stok)</h3>
                    <button type="button" class="btn btn-success" id="addVariantBtn">
                        <i class="fas fa-plus"></i> Tambah Varian
                    </button>
                </div>
                <div class="variant-row merch-variant" style="margin-bottom: 5px;">
                    <label>Ukuran (Size)</label>
                    <label class="col-grind">(Grind)</label> <label>Harga (Rp)</label>
                    <label>Stok</label>
                    <label>Aksi</label>
                </div>
                <div id="variantContainer">
                    <div class="variant-row merch-variant">
                        <select name="varian_size[]" required>
                            <option value="">-- Pilih Size --</option>
                            <?php foreach ($size_options as $option): ?>
                                <option value="<?= $option['id_size'] ?>"><?= htmlspecialchars($option['ukuran']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="varian_grind[]" class="col-grind" style="background:#eee; pointer-events:none;">
                            <option value="">(N/A)</option>
                        </select>
                        <input type="number" name="varian_harga[]" placeholder="150000" required>
                        <input type="number" name="varian_stok[]" placeholder="50" required>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 1)" disabled>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<template id="variantTemplate">
    <div class="variant-row merch-variant">
        <input type="hidden" name="varian_id[]" value="new">
        <select name="varian_size[]" required>
            <option value="">-- Pilih Size --</option>
            <?php foreach ($size_options as $option): ?>
                <option value="<?= $option['id_size'] ?>"><?= htmlspecialchars($option['ukuran']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="varian_grind[]" class="col-grind" style="background:#eee; pointer-events:none;">
            <option value="">(N/A)</option>
        </select>
        <input type="number" name="varian_harga[]" placeholder="150000" required>
        <input type="number" name="varian_stok[]" placeholder="50" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 1)">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initVariantForm('addVariantBtn', 'variantContainer', 'variantTemplate');
    });
</script>
<?php include("../../Component/bottom.php"); ?>

