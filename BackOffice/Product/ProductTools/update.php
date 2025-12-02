<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID Produk tidak valid.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];
$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE id_produk = ? AND id_kategori IN (4, 6)");
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$produk_result = $stmt_produk->get_result();
if ($produk_result->num_rows === 0) {
    $_SESSION['message'] = "Produk (Alat/Rekomendasi) tidak ditemukan.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}
$produk = $produk_result->fetch_assoc();
$stmt_produk->close();
$kategori_query = "SELECT * FROM kategori WHERE id_kategori IN (4, 6)";
$kategori_result = mysqli_query($conn, $kategori_query);
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-edit"></i> Edit Alat / Rekomendasi</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <form class="form-container" action="action/update.php" method="POST" enctype="multipart/form-data" id="editToolForm">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
            <h3>Informasi Utama Produk</h3>
            <div class="form-row">
                <div>
                    <label for="nama_produk">Nama Produk</label>
                    <input type="text" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>
                <div>
                    <label for="id_kategori">Kategori Produk</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)): ?>
                            <option value="<?= $kategori['id_kategori'] ?>" <?= ($produk['id_kategori'] == $kategori['id_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <label>Gambar Menu (Opsional)</label>
            <small style="color:#666; display:block; margin-bottom:8px;">
                * Klik gambar untuk mengubah. Kosongkan jika tidak ingin mengubah.
            </small>
            <input
                type="file"
                id="fileInputTools"
                name="gambar_url"
                accept="image/png, image/jpeg, image/webp"
                onchange="handleImagePreview(this, 'imagePreviewTools')"
                style="display:none;">
            <div id="imagePreviewTools"
                class="image-preview-single"
                onclick="document.getElementById('fileInputTools').click()"
                style="display:flex; width: 200px; height: 200px;">
                <img src="<?= htmlspecialchars(str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url'])) ?>" alt="Preview">
            </div>
            <label for="link">Link Eksternal (Tokopedia/Shopee/dll)</label>
            <input type="text" id="link" name="link" value="<?= htmlspecialchars($produk['link']) ?>" placeholder="https://..." required>
            <label for="deskripsi">Deskripsi Singkat Produk</label>
            <textarea id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
            <input type="hidden" name="origin" value="">
            <input type="hidden" name="altitude" value="">
            <input type="hidden" name="variety" value="">
            <input type="hidden" name="process" value="">
            <input type="hidden" name="notes" value="">
            <input type="hidden" name="varian_id[]" value="">
            <input type="hidden" name="delete_variants" value="">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?php include("../../Component/bottom.php"); ?>

