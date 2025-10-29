<?php
include("../../Koneksi/koneksi.php");
include("../Component/session.php");
include("../Component/head.php");

// Ambil ID dari URL
$id_galery = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_galery <= 0) {
    echo "<script>
        alert('ID Galeri tidak valid!');
        window.location.href = 'index.php';
    </script>";
    exit;
}

// Ambil data galeri
$stmt = $conn->prepare("SELECT * FROM galery WHERE id_galery = ?");
$stmt->bind_param("i", $id_galery);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
        alert('Data galeri tidak ditemukan!');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();

// Ambil gambar yang ada
$stmt_img = $conn->prepare("SELECT id_detail_galery, gambar FROM detail_galery WHERE id_galery = ?");
$stmt_img->bind_param("i", $id_galery);
$stmt_img->execute();
$result_img = $stmt_img->get_result();
$existing_images = $result_img->fetch_all(MYSQLI_ASSOC);
$stmt_img->close();
?>

<link rel="stylesheet" href="../assets/css/gallery.css">

<div class="container">
    <?php include("../Component/sidebar.php"); ?>

    <div class="form-container light">
        <h1><i class="fas fa-edit"></i> Edit Galeri</h1>

        <form id="galleryUpdateForm" enctype="multipart/form-data">
            <input type="hidden" name="id_galery" value="<?php echo htmlspecialchars($id_galery); ?>">

            <label>Judul Galeri <span style="color:red;">*</span></label>
            <input type="text"
                name="judul"
                maxlength="50"
                required
                value="<?php echo htmlspecialchars($data['judul']); ?>"
                placeholder="Masukkan judul galeri">

            <label>Deskripsi <span style="color:red;">*</span></label>
            <textarea name="deskripsi"
                rows="4"
                required
                placeholder="Masukkan deskripsi galeri"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>

            <label>Tanggal <span style="color:red;">*</span></label>
            <input type="date"
                name="tanggal"
                id="tanggal"
                required
                value="<?php echo htmlspecialchars($data['tanggal']); ?>">

            <!-- Tampilkan gambar yang sudah ada -->
            <?php if (count($existing_images) > 0): ?>
                <label>Gambar Saat Ini</label>
                <div class="image-preview-grid" style="margin-bottom: 15px;">
                    <?php foreach ($existing_images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img['gambar']); ?>"
                            alt="Gambar Galeri"
                            style="width:100px; height:80px; object-fit:cover; border-radius:8px;">
                    <?php endforeach; ?>
                </div>
                <small style="color:#666; display:block; margin-bottom:10px;">
                    <i class="fas fa-info-circle"></i> Total: <?php echo count($existing_images); ?> gambar
                </small>
            <?php endif; ?>

            <label>Upload Gambar Baru (Opsional)</label>
            <small style="color:#666; display:block; margin-bottom:5px;">
                * Jika diisi, akan <strong>mengganti semua gambar lama</strong>. Maksimal 4 gambar baru.
            </small>
            <input type="file"
                name="gambar[]"
                multiple
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                onchange="previewGalleryImages(this)">

            <div id="previewContainer" class="image-preview-grid"></div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Galeri
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