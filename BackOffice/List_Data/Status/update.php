<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$id_status = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_status <= 0) {
    echo "<script>alert('ID Status tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM status WHERE id_status = ?");
$stmt->bind_param("i", $id_status);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data Status tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Status</h1>

        <form id="StatusUpdateForm">
            <input type="hidden" name="id_status" value="<?= $id_status ?>">

            <label>Nama Status</label>
            <input type="text" name="nama_status" maxlength="50" required
                value="<?= htmlspecialchars($data['nama_status']) ?>"
                placeholder="Masukkan nama status">

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

<script src="../../assets/js/status.js"></script>
<?php include("../../Component/bottom.php"); ?>