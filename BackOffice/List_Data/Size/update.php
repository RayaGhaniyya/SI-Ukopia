<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$id_size = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_size <= 0) {
    echo "<script>alert('ID Size tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM size WHERE id_size = ?");
$stmt->bind_param("i", $id_size);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data Size tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Size</h1>

        <form id="SizeUpdateForm">
            <input type="hidden" name="id_size" value="<?= $id_size ?>">

            <label>Ukuran</label>
            <input type="text" name="ukuran" maxlength="50" required
                value="<?= htmlspecialchars($data['ukuran']) ?>"
                placeholder="Masukkan ukuran">

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

<?php include("../../Component/bottom.php"); ?>