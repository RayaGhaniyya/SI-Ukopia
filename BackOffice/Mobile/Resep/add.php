<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$customers = mysqli_query($conn, "SELECT uid, nama FROM akun_customer ORDER BY nama ASC");
$alats = mysqli_query($conn, "SELECT id_alat, nama_alat FROM alat ORDER BY nama_alat ASC");

$metodes = mysqli_query($conn, "SELECT id_metode, nama_metode FROM metode ORDER BY nama_metode ASC");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="form-container" style="max-width: 800px;">
            <h1><i class="fas fa-plus-circle"></i> Buat Resep Baru</h1>
            
            <form id="resepAddForm">
                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Nama Resep <span style="color:red">*</span></label>
                        <input type="text" name="nama_resep" required placeholder="Ex: V60 Japanese Iced">
                    </div>
                    <div style="flex:1;">
                        <label>Pemilik Resep <span style="color:red">*</span></label>
                        <select name="uid_akun" required>
                            <option value="">-- Pilih Customer --</option>
                            <?php while($c = mysqli_fetch_assoc($customers)): ?>
                                <option value="<?= $c['uid'] ?>"><?= $c['nama'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Metode Seduh <span style="color:red">*</span></label>
                    <select name="id_metode" required>
                        <option value="">-- Pilih Metode --</option>
                        <?php while($m = mysqli_fetch_assoc($metodes)): ?>
                            <option value="<?= $m['id_metode'] ?>"><?= $m['nama_metode'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Jumlah Kopi (gram)</label>
                        <input type="number" name="jumlah_kopi" required placeholder="15">
                    </div>
                    <div style="flex:1; margin-right:10px;">
                        <label>Jumlah Air (ml)</label>
                        <input type="number" name="jumlah_air" required placeholder="225">
                    </div>
                    <div style="flex:1;">
                        <label>Suhu Air (°C)</label>
                        <input type="number" name="suhu" required placeholder="90">
                    </div>
                </div>
                
                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Grind Size</label>
                        <input type="text" name="ukuran_gilingan" required placeholder="Ex: Medium">
                    </div>
                    <div style="flex:1;">
                        <label>Waktu (detik)</label>
                        <input type="number" name="waktu_ekstraksi" required placeholder="120">
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Berat Minuman (gr)</label>
                        <input type="number" name="berat_minuman" placeholder="200">
                    </div>
                    <div style="flex:1;">
                        <label>TDS (Integer)</label>
                        <input type="number" name="tds" placeholder="1">
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4" required></textarea>
                </div>

                <div class="form-group" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                    <label style="display:block; margin-bottom:10px; font-weight:bold;">Pilih Alat:</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <?php while($a = mysqli_fetch_assoc($alats)): ?>
                            <label style="font-weight: normal; cursor: pointer;">
                                <input type="checkbox" name="alat[]" value="<?= $a['id_alat'] ?>"> 
                                <?= htmlspecialchars($a['nama_alat']) ?>
                            </label>
                        <?php endwhile; ?>
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
<script src="../../assets/js/Mobile/resep.js"></script>
<?php include("../../Component/bottom.php"); ?>