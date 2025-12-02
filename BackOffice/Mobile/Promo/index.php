<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/SI-Ukopia/BackOffice/Mobile/Uploads/Promo/";
$result = $conn->query("SELECT * FROM promo ORDER BY created_at DESC");
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-percent"></i> Manajemen Promo</h1>
            <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Upload Promo</a>
        </div>
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Banner Promo</th>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <img src="<?= $BASE_IMAGE_URL . $row['gambar'] ?>" 
                                 style="width:150px; height:auto; border-radius:8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        </td>
                        <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                        <td>
                            <button onclick="deletePromo(<?= $row['id_promo'] ?>)" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function deletePromo(id) {
    if(confirm('Yakin ingin menghapus promo ini?')) {
        const fd = new FormData(); fd.append('id', id);
        fetch('action/delete.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { alert(d.message); if(d.success) location.reload(); });
    }
}
</script>
<?php include("../../Component/bottom.php"); ?>
