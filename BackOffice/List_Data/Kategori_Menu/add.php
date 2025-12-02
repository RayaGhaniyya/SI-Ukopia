<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Tambah Kategori</h1>
        <form id="kategoriMenuAddForm">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" maxlength="100" required
                placeholder="Masukkan nama kategori">
            <div class="form-group" style="margin-top: 15px;">
                <label>Menggunakan Biji Kopi?</label>
                <select name="biji" class="form-control" required>
                    <option value="1">Ya (Perlu Input Beans)</option>
                    <option value="0">Tidak</option>
                </select>
                <small style="color: #666;">Pilih "Ya" jika kategori ini membutuhkan data jenis biji kopi saat input loyalty.</small>
            </div>
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
<script src="../../assets/js/kategori_menu.js"></script>
<?php include("../../Component/bottom.php"); ?>
