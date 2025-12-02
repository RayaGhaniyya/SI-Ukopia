<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id_produk = (int)$_GET['id'];

// Ambil Data
$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$produk = $stmt_produk->get_result()->fetch_assoc();
$stmt_produk->close();

// Ambil Varian & Galeri
$variants = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM detail_produk WHERE id_produk = $id_produk"), MYSQLI_ASSOC);
$galeri_items = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM produk_galeri WHERE id_produk = $id_produk"), MYSQLI_ASSOC);
$size_options = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM size ORDER BY ukuran ASC"), MYSQLI_ASSOC);
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

            <h3>Informasi Produk</h3>
            <div class="form-row">
                <div>
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>
                <div>
                    <label>Gambar Utama</label>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <img src="<?= str_replace("localhost", $_SERVER['HTTP_HOST'], $produk['gambar_url']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                        <input type="file" name="gambar_url" accept="image/*">
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 10px; border: 1px solid #eee;">
                <label style="font-weight:bold;">Galeri Foto</label>

                <?php if (count($galeri_items) > 0): ?>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <?php foreach ($galeri_items as $foto):
                            $url_foto = str_replace("localhost", $_SERVER['HTTP_HOST'], $foto['gambar_url']);
                        ?>
                            <div style="position: relative; width: 80px; height: 80px;">
                                <img src="<?= $url_foto ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px; border:1px solid #ddd;">
                                <a href="action/delete_galeri.php?id=<?= $foto['id_galeri'] ?>&id_produk=<?= $id_produk ?>"
                                    onclick="return confirm('Hapus foto ini?')"
                                    style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 12px; text-decoration: none;">&times;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <label>Tambah Foto Baru (Bisa Banyak)</label>
                <input type="file" name="galeri[]" multiple accept="image/*" class="form-control">
            </div>

            <label style="margin-top:15px;">Deskripsi</label>
            <textarea name="deskripsi" rows="3"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>

            <div class="variant-section mt-4">
                <h3>Varian Produk</h3>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="delete_variants" id="deleteVariantsInput" value="">

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?php include("../../Component/bottom.php"); ?>