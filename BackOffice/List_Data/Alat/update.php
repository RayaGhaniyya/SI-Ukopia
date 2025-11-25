<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM alat WHERE id_alat = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// Base URL Gambar
$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/si-ukopia/BackOffice/List_Data/Uploads/Alat/";

$kategori = mysqli_query($conn, "SELECT * FROM kategori_alat ORDER BY nama_kategori_alat ASC");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="form-container">
            <h1><i class="fas fa-edit"></i> Edit Alat</h1>

            <form id="alatUpdateForm" enctype="multipart/form-data">
                <input type="hidden" name="id_alat" value="<?= $id ?>">

                <label>Nama Alat</label>
                <input type="text" name="nama_alat" required value="<?= htmlspecialchars($data['nama_alat']) ?>">

                <div class="form-row">
                    <div>
                        <label>Gambar (Biarkan jika tidak diubah)</label>
                        <input type="file" id="fileInput" name="gambar" accept="image/*" style="display:none;" 
                               onchange="handleImagePreviewAlat(this, 'previewBox', 'btnUpload')">
                        
                        <div id="previewBox" onclick="document.getElementById('fileInput').click()" 
                             style="border:2px dashed #ddd; padding:10px; text-align:center; cursor:pointer; border-radius:8px;">
                            <img src="<?= $BASE_IMAGE_URL . $data['gambar'] ?>" style="width:100px; height:100px; object-fit:cover; border-radius:5px;">
                        </div>
                        
                        <button type="button" id="btnUpload" class="btn btn-info btn-sm" style="margin-top:10px; width:100%;" 
                                onclick="document.getElementById('fileInput').click()">Ganti Gambar</button>
                    </div>

                    <div>
                        <label>Kategori</label>
                        <select name="id_kategori" required>
                            <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $k['id_kategori_alat'] ?>" <?= ($k['id_kategori_alat'] == $data['id_kategori_alat']) ? 'selected' : '' ?>>
                                    <?= $k['nama_kategori_alat'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    <a href="index.php" class="btn btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/alat.js"></script>
<?php include("../../Component/bottom.php"); ?>