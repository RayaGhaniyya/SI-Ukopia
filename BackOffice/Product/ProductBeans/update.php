<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];

$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id_produk"));
$variants = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM detail_produk WHERE id_produk = $id_produk"), MYSQLI_ASSOC);
$galeri_items = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM produk_galeri WHERE id_produk = $id_produk"), MYSQLI_ASSOC);

$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);
$grind_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM grind_size ORDER BY nama_grind ASC"), MYSQLI_ASSOC);
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
                <div>
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>

                    <label>Kategori</label>
                    <select name="id_kategori" required>
                        <?php foreach ($kategori_options as $k): ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $produk['id_kategori'] == $k['id_kategori'] ? 'selected' : '' ?>>
                                <?= $k['nama_kategori'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Gambar Utama</label>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <img src="<?= str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                        <input type="file" name="gambar_url" accept="image/*">
                    </div>
                </div>
                <div>
                    <label>Origin</label> <input type="text" name="origin" value="<?= htmlspecialchars($produk['origin']) ?>">
                    <label>Altitude</label> <input type="text" name="altitude" value="<?= htmlspecialchars($produk['altitude']) ?>">
                    <label>Variety</label> <input type="text" name="variety" value="<?= htmlspecialchars($produk['variety']) ?>">
                </div>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 12px; border: 1px solid #eee;">
                <h4 style="font-size: 1rem; margin-bottom: 15px; color: #333;"><i class="fas fa-images"></i> Galeri Foto Tambahan</h4>

                <?php if (count($galeri_items) > 0): ?>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <?php foreach ($galeri_items as $foto):
                            $url_foto = str_replace("localhost", $_SERVER['HTTP_HOST'], $foto['gambar_url']);
                        ?>
                            <div style="position: relative; width: 80px; height: 80px;">
                                <img src="<?= $url_foto ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px; border:1px solid #ddd;">
                                <a href="action/delete_galeri.php?id=<?= $foto['id_galeri'] ?>&id_produk=<?= $id_produk ?>"
                                    onclick="return confirm('Hapus foto ini?')"
                                    style="position: absolute; top: -8px; right: -8px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 12px; text-decoration: none;">&times;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <label>Tambah Foto Baru</label>
                <input type="file" name="galeri[]" multiple accept="image/*" class="form-control">
            </div>

            <label class="mt-3">Process</label> <input type="text" name="process" value="<?= htmlspecialchars($produk['process']) ?>">
            <label>Taste Notes</label> <input type="text" name="notes" value="<?= htmlspecialchars($produk['notes']) ?>">
            <label>Deskripsi</label> <textarea name="deskripsi" rows="3"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
            <input type="hidden" name="link" value="">

            <div class="variant-section mt-4">
                <div class="variant-header">
                    <h3>Varian Produk</h3><button type="button" class="btn btn-success" id="addVariantBtn"><i class="fas fa-plus"></i> Tambah</button>
                </div>
                <div id="variantContainer">
                    <?php foreach ($variants as $variant): ?>
                        <div class="variant-row">
                            <input type="hidden" name="varian_id[]" value="<?= $variant['id_detail_produk'] ?>">
                            <select name="varian_size[]" required>
                                <?php foreach ($size_options as $opt): ?><option value="<?= $opt['id_size'] ?>" <?= $variant['id_size'] == $opt['id_size'] ? 'selected' : '' ?>><?= $opt['ukuran'] ?></option><?php endforeach; ?>
                            </select>
                            <select name="varian_grind[]" required>
                                <?php foreach ($grind_options as $opt): ?><option value="<?= $opt['id_grind'] ?>" <?= $variant['id_grind'] == $opt['id_grind'] ? 'selected' : '' ?>><?= $opt['nama_grind'] ?></option><?php endforeach; ?>
                            </select>
                            <input type="number" name="varian_harga[]" value="<?= $variant['harga'] ?>" required>
                            <input type="number" name="varian_stok[]" value="<?= $variant['stok'] ?>" required>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="delete_variants" id="deleteVariantsInput" value="">

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
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
        initVariantForm('addVariantBtn', 'variantContainer', 'variantTemplate');
    });
</script>
<?php include("../../Component/bottom.php"); ?>