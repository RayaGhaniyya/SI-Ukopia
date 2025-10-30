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
                        <?php
                        // Fix path gambar
                        $imgPath = $img['gambar'];
                        if (!file_exists("../" . $imgPath)) {
                            $imgPath = str_replace('assets/', '../assets/', $imgPath);
                        } else {
                            $imgPath = "../" . $imgPath;
                        }
                        ?>
                        <div style="position: relative; display: inline-block;">
                            <img src="<?php echo htmlspecialchars($imgPath); ?>"
                                alt="Gambar Galeri"
                                style="width:100px; height:80px; object-fit:cover; border-radius:8px; border: 2px solid #e5e7eb;"
                                onerror="this.style.background='#f3f4f6'; this.alt='Error loading image';">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px; margin-bottom: 15px; border-radius: 6px;">
                    <small style="color: #1e40af; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-info-circle"></i>
                        Total: <strong><?php echo count($existing_images); ?> gambar</strong>
                    </small>
                </div>
            <?php endif; ?>

            <label>Upload Gambar Baru (Opsional)</label>
            <small style="color:#666; display:block; margin-bottom:8px;">
                <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
                Jika diisi, akan <strong>mengganti semua gambar lama</strong>. Maksimal 4 gambar baru.<br>
                Format: JPG, JPEG, PNG, GIF, WEBP. Max 5MB per file.
            </small>

            <!-- Hidden file input -->
            <input type="file"
                id="fileInput"
                name="gambar[]"
                multiple
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                onchange="addMoreImages(this)"
                style="display: none;">

            <!-- Custom button untuk trigger file input -->
            <button type="button"
                class="btn btn-info"
                onclick="document.getElementById('fileInput').click()"
                style="margin-bottom: 10px;">
                <i class="fas fa-plus"></i> Pilih Gambar Baru (Max 4)
            </button>

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

<?php include("../Component/bottom.php"); ?>