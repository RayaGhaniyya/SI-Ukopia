<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="form-container">
            <h1><i class="fas fa-plus-circle"></i> Tambah Metode</h1>
            
            <form id="metodeAddForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Metode <span style="color:red">*</span></label>
                    <input type="text" name="nama_metode" required placeholder="Ex: V60, Aeropress">
                </div>

                <div class="form-group">
                    <label>Icon Metode (SVG/PNG) <span style="color:red">*</span></label>
                    <input type="file" id="fileInput" name="gambar" accept=".svg,.png,.jpg,.jpeg,.webp" 
                           onchange="handleImagePreview(this, 'previewBox', 'btnUpload')" style="display:none;" required>
                    
                    <div id="previewBox" onclick="document.getElementById('fileInput').click()" 
                         style="border:2px dashed #ddd; padding:20px; text-align:center; cursor:pointer; border-radius:8px;">
                        <i class="fas fa-image" style="font-size:24px; color:#ccc;"></i><br>Klik pilih gambar
                    </div>
                    <button type="button" id="btnUpload" class="btn btn-info btn-sm" style="margin-top:10px; width:100%; display:none;" 
                            onclick="document.getElementById('fileInput').click()">Ganti Gambar</button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="index.php" class="btn btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/metode.js"></script>
<?php include("../../Component/bottom.php"); ?>