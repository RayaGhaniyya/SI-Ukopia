<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];


$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$produk) {
    header("Location: index.php");
    exit;
}


$stmt_var = $conn->prepare("SELECT * FROM detail_produk WHERE id_produk = ? ORDER BY id_detail_produk ASC");
$stmt_var->bind_param("i", $id_produk);
$stmt_var->execute();
$variants = $stmt_var->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_var->close();


$stmt_gal = $conn->prepare("SELECT * FROM produk_galeri WHERE id_produk = ?");
$stmt_gal->bind_param("i", $id_produk);
$stmt_gal->execute();
$gallery = $stmt_gal->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_gal->close();


$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori = 3");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-edit"></i> Edit Merchandise</h1>
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
                        <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                            <option value="<?= $kat['id_kategori'] ?>" selected><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Gambar Utama (Thumbnail)</label>
                    <small style="color:#666; display:block; margin-bottom:5px;">* Klik gambar untuk mengganti.</small>

                    <input type="file" id="mainFileInput" name="gambar_url" accept="image/*" style="display:none;"
                        onchange="handleImagePreview(this, 'mainPreviewImg')">

                    <div onclick="document.getElementById('mainFileInput').click()"
                        style="width: 150px; height: 150px; border: 1px dashed #ccc; cursor: pointer; overflow: hidden; border-radius: 8px; position: relative;">

                        <img id="mainPreviewImg"
                            src="<?= str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url']) ?>"
                            style="width:100%; height:100%; object-fit:cover;">

                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.5); color: white; text-align: center; font-size: 10px; padding: 2px;">
                            Klik Ubah
                        </div>
                    </div>
                </div>
                <div>
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="8"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                </div>
            </div>

            <div class="variant-section" style="margin-top: 30px;">
                <div class="variant-header">
                    <h3>Galeri Foto Tambahan</h3>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                    <?php foreach ($gallery as $img): ?>
                        <div class="gallery-item" id="gal-<?= $img['id_galeri'] ?>" style="position: relative; width: 100px; height: 100px;">
                            <img src="<?= str_replace("localhost", $_SERVER['HTTP_HOST'], $img['gambar_url']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                            <button type="button" onclick="markGalleryForDelete(<?= $img['id_galeri'] ?>)"
                                style="position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer;">
                                &times;
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="delete_gallery_ids" id="deleteGalleryInput" value="">

                <label>Tambah Foto Baru (Bisa pilih banyak)</label>
                <input type="file" id="galeriInput" name="galeri_files[]" multiple accept="image/*" class="form-control"
                    onchange="handleGalleryPreview(this, 'newGalleryPreview')">

                <div id="newGalleryPreview" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;"></div>
            </div>

            <div class="variant-section">
                <div class="variant-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Varian Stok & Harga</h3>
                    <button type="button" class="btn btn-success" id="addVariantBtn"><i class="fas fa-plus"></i> Tambah Size</button>
                </div>

                <div class="variant-row" style="font-weight: bold;">
                    <label>Ukuran</label>
                    <label>Harga (Rp)</label>
                    <label>Stok</label>
                    <label>Aksi</label>
                </div>

                <div id="variantContainer">
                    <?php foreach ($variants as $variant): ?>
                        <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="hidden" name="varian_id[]" value="<?= $variant['id_detail_produk'] ?>">

                            <select name="varian_size[]" required style="flex: 1;">
                                <?php foreach ($size_options as $opt): ?>
                                    <option value="<?= $opt['id_size'] ?>" <?= ($variant['id_size'] == $opt['id_size']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opt['ukuran']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="number" name="varian_harga[]" value="<?= $variant['harga'] ?>" required style="flex: 1;">
                            <input type="number" name="varian_stok[]" value="<?= $variant['stok'] ?>" required style="width: 100px;">

                            <button type="button" class="btn btn-danger" onclick="removeVariant(this, false)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="delete_variants" id="deleteVariantsInput" value="">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<template id="variantTemplate">
    <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
        <input type="hidden" name="varian_id[]" value="new">
        <select name="varian_size[]" required style="flex: 1;">
            <option value="">-- Pilih Size --</option>
            <?php foreach ($size_options as $opt): ?>
                <option value="<?= $opt['id_size'] ?>"><?= htmlspecialchars($opt['ukuran']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="varian_harga[]" placeholder="Harga" required style="flex: 1;">
        <input type="number" name="varian_stok[]" placeholder="Stok" required style="width: 100px;">
        <button type="button" class="btn btn-danger" onclick="removeVariant(this, true)"><i class="fas fa-trash"></i></button>
    </div>
</template>

<script>
    
    function handleImagePreview(input, imgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(imgId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    
    function handleGalleryPreview(input, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; 

        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.style.cssText = "width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;";
                    div.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    
    document.getElementById('addVariantBtn').addEventListener('click', () => {
        const clone = document.getElementById('variantTemplate').content.cloneNode(true);
        document.getElementById('variantContainer').appendChild(clone);
    });

    function removeVariant(btn, isNew) {
        const row = btn.closest('.variant-row');
        if (!isNew) {
            const id = row.querySelector('input[name="varian_id[]"]').value;
            const input = document.getElementById('deleteVariantsInput');
            let vals = input.value ? input.value.split(',') : [];
            vals.push(id);
            input.value = vals.join(',');
        }
        row.remove();
    }

    
    function markGalleryForDelete(id) {
        if (confirm('Hapus foto ini? (Akan terhapus permanen setelah klik Simpan)')) {
            const input = document.getElementById('deleteGalleryInput');
            let vals = input.value ? input.value.split(',') : [];
            vals.push(id);
            input.value = vals.join(',');

            document.getElementById('gal-' + id).style.display = 'none';
        }
    }
</script>

<?php include("../../Component/bottom.php"); ?>