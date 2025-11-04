<?php
// [UBAH] Path koneksi sesuai lokasi folder
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <!-- [UBAH] Icon dan title form -->
        <h1><i class="fas fa-plus-circle"></i> Tambah Grind Size</h1>

        <!-- [UBAH] Form ID untuk JS -->
        <form id="GrindSizeAddForm">
            <!-- [UBAH] Label dan name input sesuai field DB -->
            <label>Grind Size</label>
            <input type="text" name="nama_grind" maxlength="100" required
                placeholder="Masukkan Grind Size">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <!-- [UBAH] Link cancel ke index -->
                <a href="index.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<!-- [UBAH] Script JS sesuai nama modul -->
<?php include("../../Component/bottom.php"); ?>