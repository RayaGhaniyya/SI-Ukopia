<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="form-container" style="max-width: 600px;">
            <h1><i class="fas fa-upload"></i> Upload Promo Baru</h1>
            
            <form id="promoForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Gambar Banner (Landscape disarankan)</label>
                    <input type="file" name="gambar" accept="image/*" required class="form-control" 
                           style="padding: 10px; border: 2px dashed #ddd;">
                    <small>Format: JPG, PNG. Max 2MB.</small>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
                <a href="index.php" class="btn btn-cancel">Kembali</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('promoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    
    fetch('action/store.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if(d.success) window.location.href='index.php';
    })
    .catch(e => alert('Gagal upload: ' + e));
});
</script>
<?php include("../../Component/bottom.php"); ?>