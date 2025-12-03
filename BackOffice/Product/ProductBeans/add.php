<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");


$kategori_query = "SELECT * FROM kategori WHERE id_kategori IN (1, 2)";
$kategori_result = mysqli_query($conn, $kategori_query);

$size_query = "SELECT * FROM size ORDER BY ukuran ASC";
$size_result = mysqli_query($conn, $size_query);
$size_options = mysqli_fetch_all($size_result, MYSQLI_ASSOC);

$grind_query = "SELECT * FROM grind_size ORDER BY nama_grind ASC";
$grind_result = mysqli_query($conn, $grind_query);
$grind_options = mysqli_fetch_all($grind_result, MYSQLI_ASSOC);
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-plus"></i> Tambah Biji Kopi</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form class="form-container" action="action/store.php" method="POST" enctype="multipart/form-data" id="addBeanForm">

            <h3>Informasi Utama Produk</h3>

            <div class="form-row">
                <div>
                    <label for="nama_produk">Nama Produk (cth: Arabica Gayo)</label>
                    <input type="text" id="nama_produk" name="nama_produk" required>

                    <label for="id_kategori">Kategori Beans</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)): ?>
                            <option value="<?= $kategori['id_kategori'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Gambar Utama Produk <span style="color:red;">*</span></label>
                    <small style="color:#666; display:block; margin-bottom:8px;">
                        * Format: JPG, JPEG, PNG, WEBP. Max 5MB.
                    </small>

                    <input type="file" id="fileInputBeans" name="gambar_url" accept="image/jpeg,image/jpg,image/png,image/webp"
                        onchange="handleImagePreview(this, 'imagePreviewBeans', 'uploadButtonBeans')" style="display:none;" required>

                    <div id="imagePreviewBeans" class="image-preview-single"
                        onclick="document.getElementById('fileInputBeans').click()"
                        style="width: 200px; height: 200px; cursor: pointer;">
                        <span>Klik untuk memilih gambar</span>
                    </div>

                    <button type="button" id="uploadButtonBeans" class="btn btn-info btn-sm"
                        onclick="document.getElementById('fileInputBeans').click()" style="margin-top:10px; width:200px; max-width:100%;">
                        <i class="fas fa-plus"></i> Pilih Gambar
                    </button>
                </div>

                <div>
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin">
                    <label for="altitude">Altitude (cth: 1200-1500 MASL)</label>
                    <input type="text" id="altitude" name="altitude">
                    <label for="variety">Variety (cth: Ateng, Timtim)</label>
                    <input type="text" id="variety" name="variety">

                    <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc;">
                        <label style="font-weight:bold;">Galeri Foto (Opsional)</label>
                        <small class="text-muted d-block mb-2">* Tahan CTRL untuk pilih banyak foto.</small>
                        <input type="file" name="galeri[]" multiple accept="image/*" class="form-control">
                    </div>
                </div>
            </div>

            <label for="process">Process (cth: Natural, Full Wash)</label>
            <input type="text" id="process" name="process">

            <label for="notes">Tasting Notes (Pisahkan dengan koma, cth: Fruity, Sweet, Chocolate)</label>
            <input type="text" id="notes" name="notes">

            <label for="deskripsi">Deskripsi Lengkap Produk</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>

            <input type="hidden" name="link" value="">

            <div class="variant-section mt-4">
                <div class="variant-header">
                    <h3>Varian Harga & Stok</h3>
                    <button type="button" class="btn btn-success" id="addVariantBtn">
                        <i class="fas fa-plus"></i> Tambah Varian
                    </button>
                </div>
                <div class="variant-row" style="margin-bottom: 5px;">
                    <label>Ukuran (Size)</label>
                    <label>Gilingan (Grind Size)</label>
                    <label>Harga (Rp)</label>
                    <label>Stok</label>
                    <label>Aksi</label>
                </div>
                <div id="variantContainer">
                    <div class="variant-row">
                        <select name="varian_size[]" required>
                            <option value="">-- Pilih Size --</option>
                            <?php foreach ($size_options as $option): ?>
                                <option value="<?= $option['id_size'] ?>"><?= htmlspecialchars($option['ukuran']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="varian_grind[]" required>
                            <option value="">-- Pilih Gilingan --</option>
                            <?php foreach ($grind_options as $option): ?>
                                <option value="<?= $option['id_grind'] ?>"><?= htmlspecialchars($option['nama_grind']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="varian_harga[]" placeholder="50000" required>
                        <input type="number" name="varian_stok[]" placeholder="100" required>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 1)" disabled>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<template id="variantTemplate">
    <div class="variant-row">
        <input type="hidden" name="varian_id[]" value="new">
        <select name="varian_size[]" required>
            <option value="">-- Pilih Size --</option>
            <?php foreach ($size_options as $option): ?>
                <option value="<?= $option['id_size'] ?>"><?= htmlspecialchars($option['ukuran']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="varian_grind[]" required>
            <option value="">-- Pilih Gilingan --</option>
            <?php foreach ($grind_options as $option): ?>
                <option value="<?= $option['id_grind'] ?>"><?= htmlspecialchars($option['nama_grind']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="varian_harga[]" placeholder="50000" required>
        <input type="number" name="varian_stok[]" placeholder="100" required>
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