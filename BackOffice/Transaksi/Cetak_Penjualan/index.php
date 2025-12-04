<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-print"></i> Laporan Penjualan</h1>
        </div>

        <div class="card p-4" style="max-width: 500px; margin: 20px auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <form action="cetak.php" method="GET" target="_blank">
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 600;">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" class="form-control" required value="<?= date('Y-m-01') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 600;">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 600;">Status Transaksi</label>
                    <select name="status" class="form-control">
                        <option value="SEMUA">Semua Transaksi Sukses</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Diproses">Diproses</option>
                    </select>
                    <small class="text-muted">*Transaksi Batal/Kadaluarsa tidak dihitung.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100" style="padding: 12px; font-weight: bold;">
                    <i class="fas fa-file-pdf"></i> GENERATE LAPORAN
                </button>
            </form>
        </div>
    </div>
</div>

<?php include("../../Component/bottom.php"); ?>