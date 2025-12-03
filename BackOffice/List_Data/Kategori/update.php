<?php

include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");


$id_kategori = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_kategori <= 0) {
    
    echo "<script>alert('ID Kategori tidak valid!'); window.location.href='index.php';</script>";
    exit;
}


$stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
$stmt->bind_param("i", $id_kategori);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    
    echo "<script>alert('Data kategori tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Kategori</h1>

        <form id="kategoriUpdateForm">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori ?>">

            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" maxlength="100" required
                value="<?= htmlspecialchars($data['nama_kategori']) ?>"
                placeholder="Masukkan nama kategori">

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

<script src="../../assets/js/kategori.js"></script>
<?php include("../../Component/bottom.php"); ?>