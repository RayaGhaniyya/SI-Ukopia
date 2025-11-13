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
$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$produk_result = $stmt_produk->get_result();
if ($produk_result->num_rows === 0) {
    $_SESSION['message'] = "Produk tidak ditemukan.";
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
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori IN (1, 2)");
$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);
$grind_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM grind_size ORDER BY nama_grind ASC"), MYSQLI_ASSOC);
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-edit"></i> Edit Biji Kopi</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form class="form-container" action="action/update.php" method="POST" enctype="multipart/form-data" id="editBeanForm">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
            <h3>Informasi Utama Produk</h3>

            <div class="form-row">
                <div>
                    <label for="nama_produk">Nama Produk</label>
                    <input type="text" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                    <label for="id_kategori">Kategori Beans</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)): ?>
                            <option value="<?= $kategori['id_kategori'] ?>" <?= ($produk['id_kategori'] == $kategori['id_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Gambar Menu (Opsional)</label>
                    <small style="color:#666; display:block; margin-bottom:8px;">
                        * Klik gambar untuk mengubah. Kosongkan jika tidak ingin mengubah.
                    </small>
                    <input
                        type="file"
                        id="fileInputBeans"
                        name="gambar_url"
                        accept="image/png, image/jpeg, image/webp"
                        onchange="handleImagePreview(this, 'imagePreviewBeans')"
                        style="display:none;">

                    <div id="imagePreviewBeans"
                        class="image-preview-single"
                        onclick="document.getElementById('fileInputBeans').click()"
                        style="display:flex; width: 200px; height: 200px;"> <img src="<?= htmlspecialchars(str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url'])) ?>" alt="Preview">
                    </div>
                </div>

                <div>
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin" value="<?= htmlspecialchars($produk['origin']) ?>">
                    <label for="altitude">Altitude</label>
                    <input type="text" id="altitude" name="altitude" value="<?= htmlspecialchars($produk['altitude']) ?>">
                    <label for="variety">Variety</label>
                    <input type="text" id="variety" name="variety" value="<?= htmlspecialchars($produk['variety']) ?>">
                </div>
            </div>

            <label for="process">Process</label>
            <input type="text" id="process" name="process" value="<?= htmlspecialchars($produk['process']) ?>">
            <label for="notes">Tasting Notes</label>
            <input type="text" id="notes" name="notes" value="<?= htmlspecialchars($produk['notes']) ?>">
            <label for="deskripsi">Deskripsi Lengkap Produk</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
            <label for="link">Link (Opsional)</label>
            <input type="text" id="link" name="link" value="<?= htmlspecialchars($produk['link']) ?>">

            <div class="variant-section">
                <div class="variant-header">
                    <h3>Varian Harga & Stok</h3>
                    <button type="button" class="btn btn-success" id="addVariantBtn">
                        <i class="fas fa-plus"></i> Tambah Varian Baru
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
                    <?php foreach ($variants as $index => $variant): ?>
                        <div class="variant-row">
                            <input type="hidden" name="varian_id[]" value="<?= $variant['id_detail_produk'] ?>">
                            <select name="varian_size[]" required>
                                <?php foreach ($size_options as $option): ?>
                                    <option value="<?= $option['id_size'] ?>" <?= ($variant['id_size'] == $option['id_size']) ? 'selected' : '' ?>><?= htmlspecialchars($option['ukuran']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="varian_grind[]" required>
                                <?php foreach ($grind_options as $option): ?>
                                    <option value="<?= $option['id_grind'] ?>" <?= ($variant['id_grind'] == $option['id_grind']) ? 'selected' : '' ?>><?= htmlspecialchars($option['nama_grind']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="varian_harga[]" value="<?= $variant['harga'] ?>" placeholder="50000" required>
                            <input type="number" name="varian_stok[]" value="<?= $variant['stok'] ?>" placeholder="100" required>
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