<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM metode WHERE id_metode = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/si-ukopia/BackOffice/List_Data/Uploads/Metode/";
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="form-container">
            <h1><i class="fas fa-edit"></i> Edit Metode</h1>
            <form id="metodeUpdateForm" enctype="multipart/form-data">
                <input type="hidden" name="id_metode" value="<?= $id ?>">
                <div class="form-group">
                    <label>Nama Metode</label>
                    <input type="text" name="nama_metode" required value="<?= htmlspecialchars($data['nama_metode']) ?>">
                </div>
                <div class="form-group">
                    <label>Icon (Biarkan jika tidak diubah)</label>
                    <input type="file" id="fileInput" name="gambar" accept=".svg,.png,.jpg,.webp" style="display:none;" 
                           onchange="handleImagePreview(this, 'previewBox', 'btnUpload')">
                    <div id="previewBox" onclick="document.getElementById('fileInput').click()" 
                         style="border:2px dashed #ddd; padding:10px; text-align:center; cursor:pointer; border-radius:8px;">
                        <?php if(!empty($data['gambar_metode'])): ?>
                            <img src="<?= $BASE_IMAGE_URL . $data['gambar_metode'] ?>" style="width:100px; height:100px; object-fit:contain;">
                        <?php else: ?>
                            Klik pilih gambar
                        <?php endif; ?>
                    </div>
                    <button type="button" id="btnUpload" class="btn btn-info btn-sm" style="margin-top:10px; width:100%;" 
                            onclick="document.getElementById('fileInput').click()">Ganti Gambar</button>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    <a href="index.php" class="btn btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../../assets/js/metode.js"></script>
<?php include("../../Component/bottom.php"); ?>
