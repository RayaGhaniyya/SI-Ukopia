<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM kategori_menu WHERE id_kategori_menu = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Kategori</h1>

        <form id="kategoriMenuUpdateForm">
            <input type="hidden" name="id_kategori_menu" value="<?= $id ?>">

            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" maxlength="100" required
                value="<?= htmlspecialchars($data['nama_kategori']) ?>">

            <div class="form-group" style="margin-top: 15px;">
                <label>Menggunakan Biji Kopi?</label>
                <select name="biji" class="form-control" required>
                    <option value="1" <?= ($data['biji'] == 1) ? 'selected' : '' ?>>Ya</option>
                    <option value="0" <?= ($data['biji'] == 0) ? 'selected' : '' ?>>Tidak</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <a href="index.php" class="btn btn-cancel"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/kategori_menu.js"></script>
<?php include("../../Component/bottom.php"); ?>