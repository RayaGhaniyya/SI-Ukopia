<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$kategori = mysqli_query($conn, "SELECT * FROM kategori_alat ORDER BY nama_kategori_alat ASC");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="form-container">
            <h1><i class="fas fa-plus-circle"></i> Tambah Alat</h1>
            
            <form id="alatAddForm" enctype="multipart/form-data">
                <label>Nama Alat <span style="color:red">*</span></label>
                <input type="text" name="nama_alat" required placeholder="Nama alat...">

                <div class="form-row">
                    <div>
                        <label>Gambar <span style="color:red">*</span></label>
                        <input type="file" id="fileInput" name="gambar" accept="image/*" style="display:none;" onchange="handleImagePreviewAlat(this, 'previewBox', 'btnUpload')" required>
                        
                        <div id="previewBox" onclick="document.getElementById('fileInput').click()" 
                             style="border:2px dashed #ddd; padding:20px; text-align:center; cursor:pointer; border-radius:8px;">
                            <i class="fas fa-image" style="font-size:24px; color:#ccc;"></i><br>Klik pilih gambar
                        </div>
                        
                        <button type="button" id="btnUpload" class="btn btn-info btn-sm" style="margin-top:10px; width:100%; display:none;" 
                                onclick="document.getElementById('fileInput').click()">Ganti Gambar</button>
                    </div>

                    <div>
                        <label>Kategori <span style="color:red">*</span></label>
                        <select name="id_kategori" required>
                            <option value="">-- Pilih --</option>
                            <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $k['id_kategori_alat'] ?>"><?= $k['nama_kategori_alat'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="index.php" class="btn btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/alat.js"></script>
<?php include("../../Component/bottom.php"); ?>