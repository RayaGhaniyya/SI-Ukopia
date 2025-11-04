<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Tambah Size</h1>

        <form id="SizeAddForm">
            <label>Ukuran</label>
            <input type="text" name="ukuran" maxlength="50" required
                placeholder="Contoh: M, L, XL">

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

<?php include("../../Component/bottom.php"); ?>