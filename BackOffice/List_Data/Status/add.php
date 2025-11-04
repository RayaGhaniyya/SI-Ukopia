<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Tambah Status</h1>

        <form id="StatusAddForm">
            <label>Nama Status</label>
            <input type="text" name="nama_status" maxlength="50" required
                placeholder="Contoh: Aktif, Nonaktif, Pending">

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