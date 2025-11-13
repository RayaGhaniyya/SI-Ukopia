<?php
// [UBAH] Path koneksi sesuai lokasi folder
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

// [UBAH] Nama parameter ID sesuai primary key
$id_kategori = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_kategori <= 0) {
    // [UBAH] Text alert dan redirect
    echo "<script>alert('ID Kategori tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

// [UBAH] Query - nama tabel dan kolom
$stmt = $conn->prepare("SELECT * FROM kategori_menu WHERE id_kategori_menu = ?");
$stmt->bind_param("i", $id_kategori);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    // [UBAH] Text alert
    echo "<script>alert('Data kategori tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$stmt->close();
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <!-- [UBAH] Icon dan title form -->
        <h1><i class="fas fa-edit"></i> Edit Kategori</h1>

        <!-- [UBAH] Form ID untuk JS -->
        <form id="kategoriMenuUpdateForm">
            <!-- [UBAH] Name hidden input sesuai primary key -->
            <input type="hidden" name="id_kategori_menu" value="<?= $id_kategori ?>">

            <!-- [UBAH] Label, name, dan value sesuai field DB -->
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" maxlength="100" required
                value="<?= htmlspecialchars($data['nama_kategori']) ?>"
                placeholder="Masukkan nama kategori">

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

<script src="../../assets/js/kategori_menu.js"></script>
<?php include("../../Component/bottom.php"); ?>