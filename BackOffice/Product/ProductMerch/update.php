<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];


$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE id_produk = ? AND id_kategori = 3");
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$produk = $stmt_produk->get_result()->fetch_assoc();
$stmt_produk->close();

if (!$produk) {
    header("Location: index.php");
    exit;
}


$variants = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM detail_produk WHERE id_produk = $id_produk"), MYSQLI_ASSOC);


$galeri_items = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM produk_galeri WHERE id_produk = $id_produk"), MYSQLI_ASSOC);


$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);
$id_kategori_merch = 3;
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-edit"></i> Edit Merchandise</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form class="form-container" action="action/update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori_merch ?>">

            <h3>Informasi Utama</h3>
            <div class="form-row">
                <div>
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>
                <div>
                    <label>Gambar Utama (Klik untuk ganti)</label>

                    <div id="previewUtama"
                        onclick="document.getElementById('inputUtama').click()"
                        style="width: 150px; height: 150px; cursor: pointer; border: 2px dashed #ccc; border-radius: 8px; overflow: hidden; position: relative;">

                        <img src="<?= str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url']) ?>"
                            style="width: 100%; height: 100%; object-fit: cover;">

                        <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: rgba(0,0,0,0.5); color: #fff; text-align: center; font-size: 10px;">
                            Klik untuk Ubah
                        </div>
                    </div>

                    <input type="file" id="inputUtama" name="gambar_url" accept="image/*" style="display: none;"
                        onchange="handleImagePreview(this, 'previewUtama')">
                </div>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; border: 1px solid #eee;">
                <label style="font-weight:bold;">Galeri Foto</label>

                <div class="d-flex gap-2 flex-wrap mb-3">
                    <?php foreach ($galeri_items as $foto): ?>
                        <div style="position: relative; width: 80px; height: 80px;">
                            <img src="<?= str_replace("localhost", $_SERVER['HTTP_HOST'], $foto['gambar_url']) ?>"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px;">
                            <a href="action/delete_galeri.php?id=<?= $foto['id_galeri'] ?>&id_produk=<?= $id_produk ?>"
                                class="btn-delete-small"
                                onclick="return confirm('Hapus foto ini?')"
                                style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 12px; text-decoration: none;">&times;</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <label class="mt-2">Tambah Foto Baru</label>
                <input type="file" name="galeri[]" multiple accept="image/*" class="form-control"
                    onchange="handleGalleryPreview(this, 'previewGaleriBaru')">

                <div id="previewGaleriBaru" class="d-flex gap-2 flex-wrap mt-3"></div>
            </div>

            <label class="mt-3">Deskripsi</label>
            <textarea name="deskripsi" rows="3"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>

            <input type="hidden" name="origin" value="">
            <input type="hidden" name="altitude" value="">
            <input type="hidden" name="variety" value="">
            <input type="hidden" name="process" value="">
            <input type="hidden" name="notes" value="">
            <input type="hidden" name="link" value="">

            <div class="variant-section mt-4">
                <div class="variant-header">
                    <h3>Varian Produk</h3>
                    <button type="button" class="btn btn-success" id="addVariantBtn"><i class="fas fa-plus"></i> Tambah</button>
                </div>
                <div id="variantContainer">
                    <?php foreach ($variants as $variant): ?>
                        <div class="variant-row merch-variant">
                            <input type="hidden" name="varian_id[]" value="<?= $variant['id_detail_produk'] ?>">
                            <select name="varian_size[]" required>
                                <?php foreach ($size_options as $option): ?>
                                    <option value="<?= $option['id_size'] ?>" <?= ($variant['id_size'] == $option['id_size']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($option['ukuran']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="varian_grind[]" style="display:none;">
                                <option value="">(N/A)</option>
                            </select>
                            <input type="number" name="varian_harga[]" value="<?= $variant['harga'] ?>" required>
                            <input type="number" name="varian_stok[]" value="<?= $variant['stok'] ?>" required>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 0)"><i class="fas fa-trash"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="delete_variants" id="deleteVariantsInput" value="">

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
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
        <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 1)"><i class="fas fa-trash"></i></button>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initVariantForm('addVariantBtn', 'variantContainer', 'variantTemplate');
    });
</script>
<?php include("../../Component/bottom.php"); ?>