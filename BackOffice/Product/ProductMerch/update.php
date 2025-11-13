<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

// (Logika PHP kamu SAMA PERSIS, tidak diubah)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID Produk tidak valid.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];
$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE id_produk = ? AND id_kategori = 3");
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$produk_result = $stmt_produk->get_result();
if ($produk_result->num_rows === 0) {
    $_SESSION['message'] = "Produk Merchandise tidak ditemukan.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}
$produk = $produk_result->fetch_assoc();
$stmt_produk->close();
$stmt_variants = $conn->prepare("SELECT * FROM detail_produk WHERE id_produk = ? ORDER BY id_detail_produk ASC");
$stmt_variants->bind_param("i", $id_produk);
$stmt_variants->execute();
$variants_result = $stmt_variants->get_result();
$variants = $variants_result->fetch_all(MYSQLI_ASSOC);
$stmt_variants->close();
$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);
$id_kategori_merch = 3;
// --- AKHIR LOGIKA PHP ---
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

        <form class="form-container" action="action/update.php" method="POST" enctype="multipart/form-data" id="editMerchForm">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori_merch ?>">
            <h3>Informasi Utama Produk</h3>

            <div class="form-row">
                <div>
                    <label for="nama_produk">Nama Produk</label>
                    <input type="text" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>
                <div>
                    <label>Gambar Menu (Opsional)</label>
                    <small style="color:#666; display:block; margin-bottom:8px;">
                        * Klik gambar untuk mengubah. Kosongkan jika tidak ingin mengubah.
                    </small>
                    <input
                        type="file"
                        id="fileInputMerch"
                        name="gambar_url"
                        accept="image/png, image/jpeg, image/webp"
                        onchange="handleImagePreview(this, 'imagePreviewMerch')"
                        style="display:none;">

                    <div id="imagePreviewMerch"
                        class="image-preview-single"
                        onclick="document.getElementById('fileInputMerch').click()"
                        style="display:flex; width: 200px; height: 200px;">
                        <img src="<?= htmlspecialchars(str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url'])) ?>" alt="Preview">
                    </div>
                </div>
            </div>

            <label for="deskripsi">Deskripsi Lengkap Produk</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>

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
                        <i class="fas fa-plus"></i> Tambah Varian Baru
                    </button>
                </div>
                <div class="variant-row merch-variant" style="margin-bottom: 5px;">
                    <label>Ukuran (Size)</label>
                    <label class="col-grind">(Grind)</label>
                    <label>Harga (Rp)</label>
                    <label>Stok</label>
                    <label>Aksi</label>
                </div>
                <div id="variantContainer">
                    <?php foreach ($variants as $index => $variant): ?>
                        <div class="variant-row merch-variant">
                            <input type="hidden" name="varian_id[]" value="<?= $variant['id_detail_produk'] ?>">
                            <select name="varian_size[]" required>
                                <?php foreach ($size_options as $option): ?>
                                    <option value="<?= $option['id_size'] ?>" <?= ($variant['id_size'] == $option['id_size']) ? 'selected' : '' ?>><?= htmlspecialchars($option['ukuran']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="varian_grind[]" class="col-grind">
                                <option value="">(N/A)</option>
                            </select>
                            <input type="number" name="varian_harga[]" value="<?= $variant['harga'] ?>" placeholder="150000" required>
                            <input type="number" name="varian_stok[]" value="<?= $variant['stok'] ?>" placeholder="50" required>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 0)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($variants)): ?>
                        <p style="text-align: center; color: #888;">Belum ada varian. Klik 'Tambah Varian Baru' untuk menambahkan.</p>
                    <?php endif; ?>
                </div>
            </div>
            <input type="hidden" name="delete_variants" id="deleteVariantsInput" value="">

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
        <select name="varian_grind[]" class="col-grind">
            <option value="">(N/A)</option>
        </select>
        <input type="number" name="varian_harga[]" placeholder="150000" required>
        <input type="number" name="varian_stok[]" placeholder="50" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeOrMarkVariant(this, 0)">
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