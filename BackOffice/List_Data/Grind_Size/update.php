<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
$id_grind = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_grind <= 0) {
    echo "<script>alert('ID Grind Size tidak valid!'); window.location.href='index.php';</script>";
    exit;
}
$stmt = $conn->prepare("SELECT * FROM grind_size WHERE id_grind = ?");
$stmt->bind_param("i", $id_grind);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) {
    echo "<script>alert('Data Grind Size tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="form-container">
        <!-- [UBAH] Icon dan title form -->
        <h1><i class="fas fa-edit"></i> Edit Grind Size</h1>
        <!-- [UBAH] Form ID untuk JS -->
        <form id="GrindSizeUpdateForm">
            <!-- [UBAH] Name hidden input sesuai primary key -->
            <input type="hidden" name="id_grind" value="<?= $id_grind ?>">
            <!-- [UBAH] Label, name, dan value sesuai field DB -->
            <label>Grind Size</label>
            <input type="text" name="nama_grind" maxlength="100" required
                value="<?= htmlspecialchars($data['nama_grind']) ?>"
                placeholder="Masukkan Grind Size">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <!-- [UBAH] Link cancel -->
                <a href="index.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script src="../../assets/js/grind.js"></script>
<?php include("../../Component/bottom.php"); ?>

