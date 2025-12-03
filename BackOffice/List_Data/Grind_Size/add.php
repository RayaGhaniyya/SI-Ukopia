<?php

include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Tambah Grind Size</h1>

        <form id="GrindSizeAddForm">
            <label>Grind Size</label>
            <input type="text" name="nama_grind" maxlength="100" required
                placeholder="Masukkan Grind Size">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="index.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/grind.js"></script>
<?php include("../../Component/bottom.php"); ?>