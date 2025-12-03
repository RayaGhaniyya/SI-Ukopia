<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");

$id_galery = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_galery <= 0) {
    echo "<script>alert('ID Galeri tidak valid!'); window.location.href='index.php';</script>";
    exit;
}


$stmt = $conn->prepare("SELECT * FROM galery WHERE id_galery = ?");
$stmt->bind_param("i", $id_galery);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data galeri tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();


$stmt_img = $conn->prepare("SELECT gambar FROM detail_galery WHERE id_galery = ?");
$stmt_img->bind_param("i", $id_galery);
$stmt_img->execute();
$existing_images = $stmt_img->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_img->close();
?>

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Galeri</h1>

        <form id="galleryUpdateForm" enctype="multipart/form-data">
            <input type="hidden" name="id_galery" value="<?= $id_galery ?>">

            <label>Judul Galeri <span style="color:red;">*</span></label>
            <input type="text" name="judul" maxlength="50" required
                value="<?= htmlspecialchars($data['judul']) ?>"
                placeholder="Masukkan judul galeri">

            <label>Deskripsi <span style="color:red;">*</span></label>
            <textarea name="deskripsi" rows="4" required
                placeholder="Masukkan deskripsi galeri"><?= htmlspecialchars($data['deskripsi']) ?></textarea>

            <label>Tanggal <span style="color:red;">*</span></label>
            <input type="date" name="tanggal" id="tanggal" required
                value="<?= $data['tanggal'] ?>">

            <?php if (count($existing_images) > 0): ?>
                <label>Gambar Saat Ini</label>
                <div class="image-preview-grid" style="margin-bottom:15px;">
                    <?php foreach ($existing_images as $img):
                        $imgPath = file_exists("../" . $img['gambar']) ? "../" . $img['gambar'] : str_replace('assets/', '../assets/', $img['gambar']);
                    ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="Gambar Galeri"
                            onerror="this.style.background='#f3f4f6'; this.alt='Error';">
                    <?php endforeach; ?>
                </div>
                <div style="background:#eff6ff; border-left:4px solid #3b82f6; padding:10px; margin-bottom:15px; border-radius:6px;">
                    <small style="color:#1e40af; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-info-circle"></i> Total: <strong><?= count($existing_images) ?> gambar</strong>
                    </small>
                </div>
            <?php endif; ?>

            <label>Upload Gambar Baru (Opsional)</label>
            <small style="color:#666; display:block; margin-bottom:8px;">
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i>
                Jika diisi, akan <strong>mengganti semua gambar lama</strong>. Max 4 gambar, 5MB/file.
            </small>

            <input type="file" id="fileInput" name="gambar[]" multiple
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                onchange="addMoreImages(this)" style="display:none;">

            <button type="button" class="btn btn-info btn-sm"
                onclick="document.getElementById('fileInput').click()"
                style="margin-bottom:10px;">
                <i class="fas fa-plus"></i> Pilih Gambar Baru (Max 4)
            </button>

            <div id="previewContainer" class="image-preview-grid"></div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="index.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/gallery.js"></script>
<?php include("../Component/bottom.php"); ?>