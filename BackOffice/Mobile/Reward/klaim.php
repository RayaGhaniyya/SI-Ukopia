<?php
include("../../../Koneksi/koneksi.php"); // Sesuaikan path
include("../../Component/session.php");
include("../../Component/head.php");
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-gift"></i> Klaim Reward Customer</h1>
        </div>
        <div class="form-container" style="max-width: 500px; margin: 50px auto;">
            <form action="action/proses_klaim.php" method="POST">
                <div class="form-group">
                    <label>Masukkan Kode Voucher</label>
                    <input type="text" name="kode_unik" class="form-control"
                        placeholder="Contoh: REW-13-5-12345" required
                        style="font-size: 20px; text-align: center; letter-spacing: 2px;">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-check-circle"></i> Validasi & Tukar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
include("../../Component/bottom.php");
?>
