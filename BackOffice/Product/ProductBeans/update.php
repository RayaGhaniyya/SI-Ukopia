<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];

// Ambil Data Produk
$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$produk = $stmt_produk->get_result()->fetch_assoc();
$stmt_produk->close();

if (!$produk) {
    header("Location: index.php");
    exit;
}

// Ambil Data Relasi
$variants = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM detail_produk WHERE id_produk = $id_produk"), MYSQLI_ASSOC);
$galeri_items = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM produk_galeri WHERE id_produk = $id_produk"), MYSQLI_ASSOC);

// Ambil Options
$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);
$grind_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM grind_size ORDER BY nama_grind ASC"), MYSQLI_ASSOC);

// INI PENTING: Ambil kategori ID 1 (Arabica) & 2 (Robusta)
$kategori_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori IN (1,2)"), MYSQLI_ASSOC);
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-edit"></i> Edit Biji Kopi</h1>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <form class="form-container" action="action/update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">

            <h3>Informasi Utama</h3>

            <div class="form-row">
                <div style="flex: 1; margin-right: 20px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px;">Nama Produk</label>
                        <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required style="width:100%; padding:8px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px;">Kategori</label>
                        <select name="id_kategori" required style="width:100%; padding:8px;">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_options as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>" <?= ($produk['id_kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                                    <?= $kat['nama_kategori'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display:block; margin-bottom:5px;">Gambar Utama</label>
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

            <div style="margin-top: 20px;">
                <h4 style="font-size: 16px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Detail Kopi</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display:block; font-size: 13px;">Origin (Asal)</label>
                        <input type="text" name="origin" value="<?= htmlspecialchars($produk['origin'] ?? '') ?>" style="width:100%; padding:6px;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 13px;">Altitude (Mdpl)</label>
                        <input type="text" name="altitude" value="<?= htmlspecialchars($produk['altitude'] ?? '') ?>" style="width:100%; padding:6px;">
                    </div>
                    <div>
                        <label style="display:block; font-size: 13px;">Variety</label>
                        <input type="text" name="variety" value="<?= htmlspecialchars($produk['variety'] ?? '') ?>" style="width:100%; padding:6px;">
                    </div>
                </div>

                <label style="display:block; margin-top: 10px;">Process</label>
                <input type="text" name="process" value="<?= htmlspecialchars($produk['process'] ?? '') ?>" style="width:100%; padding:8px;">

                <label style="display:block; margin-top: 10px;">Taste Notes</label>
                <input type="text" name="notes" value="<?= htmlspecialchars($produk['notes'] ?? '') ?>" style="width:100%; padding:8px;">

                <label style="display:block; margin-top: 10px;">Deskripsi</label>
                <textarea name="deskripsi" rows="3" style="width:100%; padding:8px;"><?= htmlspecialchars($produk['deskripsi'] ?? '') ?></textarea>

                <input type="hidden" name="link" value="">
            </div>

            <div class="variant-section mt-4">
                <div class="variant-header">
                    <h3>Varian Produk</h3>
                    <button type="button" class="btn btn-success" id="addVariantBtn"><i class="fas fa-plus"></i> Tambah</button>
                </div>
                <div id="variantContainer">
                    <?php foreach ($variants as $variant): ?>
                        <div class="variant-row">
                            <input type="hidden" name="varian_id[]" value="<?= $variant['id_detail_produk'] ?>">
                            <select name="varian_size[]" required>
                                <?php foreach ($size_options as $opt): ?>
                                    <option value="<?= $opt['id_size'] ?>" <?= ($variant['id_size'] == $opt['id_size']) ? 'selected' : '' ?>><?= $opt['ukuran'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="varian_grind[]" required>
                                <?php foreach ($grind_options as $opt): ?>
                                    <option value="<?= $opt['id_grind'] ?>" <?= $variant['id_grind'] == $opt['id_grind'] ? 'selected' : '' ?>><?= $opt['nama_grind'] ?></option>
                                <?php endforeach; ?>
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
    <div class="variant-row">
        <input type="hidden" name="varian_id[]" value="new">
        <select name="varian_size[]" required>
            <option value="">-- Size --</option><?php foreach ($size_options as $opt): ?><option value="<?= $opt['id_size'] ?>"><?= $opt['ukuran'] ?></option><?php endforeach; ?>
        </select>
        <select name="varian_grind[]" required>
            <option value="">-- Grind --</option><?php foreach ($grind_options as $opt): ?><option value="<?= $opt['id_grind'] ?>"><?= $opt['nama_grind'] ?></option><?php endforeach; ?>
        </select>
        <input type="number" name="varian_harga[]" required><input type="number" name="varian_stok[]" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 1)"><i class="fas fa-trash"></i></button>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initVariantForm === 'function') {
            initVariantForm('addVariantBtn', 'variantContainer', 'variantTemplate');
        }

        // Script preview image sederhana (Jaga-jaga jika belum ada di file JS utama)
        window.handleImagePreview = function(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById(previewId);
                    container.innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
                }
                reader.readAsDataURL(input.files[0]);
            }
        };
    });
</script>
<?php include("../../Component/bottom.php"); ?>