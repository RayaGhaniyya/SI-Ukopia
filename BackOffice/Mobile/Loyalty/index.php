<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-search-plus"></i> Input Loyalty Point</h1>
        </div>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="max-width: 500px; margin: 20px auto; text-align: center;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <div class="form-container" style="max-width: 500px; margin: 50px auto;">
            <form action="action/check_customer.php" method="POST">
                <div class="form-group">
                    <label>Cari Customer</label>
                    <input type="text" name="keyword" class="form-control" 
                        placeholder="Masukkan Email / Username" required 
                        style="font-size: 20px; text-align: center; letter-spacing: 1px;">
                    <small style="display:block; text-align:center; color:#666; margin-top:5px;">
                        *Sistem akan mencari berdasarkan No HP atau Username
                    </small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Cari Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
include("../../Component/bottom.php");
?>
