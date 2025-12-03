<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;


$stmt = $conn->prepare("SELECT * FROM resep WHERE id_resep = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    echo "<script>alert('Resep tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}


$selected_alat = [];
$res_detail = $conn->query("SELECT id_alat FROM resep_detail_alat WHERE id_resep = $id");
while($row = $res_detail->fetch_assoc()){
    $selected_alat[] = $row['id_alat'];
}


$customers = mysqli_query($conn, "SELECT uid, nama FROM akun_customer ORDER BY nama ASC");
$alats = mysqli_query($conn, "SELECT id_alat, nama_alat FROM alat ORDER BY nama_alat ASC");

$metodes = mysqli_query($conn, "SELECT id_metode, nama_metode FROM metode ORDER BY nama_metode ASC");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="form-container" style="max-width: 800px;">
            <h1><i class="fas fa-edit"></i> Edit Resep</h1>
            
            <form id="resepUpdateForm">
                <input type="hidden" name="id_resep" value="<?= $id ?>">

                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Nama Resep</label>
                        <input type="text" name="nama_resep" required value="<?= htmlspecialchars($data['nama_resep']) ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Pemilik Resep</label>
                        <select name="uid_akun" required>
                            <?php while($c = mysqli_fetch_assoc($customers)): ?>
                                <option value="<?= $c['uid'] ?>" <?= ($c['uid'] == $data['uid_akun'])?'selected':'' ?>>
                                    <?= $c['nama'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Metode Seduh <span style="color:red">*</span></label>
                    <select name="id_metode" required>
                        <option value="">-- Pilih Metode --</option>
                        <?php while($m = mysqli_fetch_assoc($metodes)): ?>
                            <option value="<?= $m['id_metode'] ?>" <?= ($m['id_metode'] == $data['id_metode']) ? 'selected' : '' ?>>
                                <?= $m['nama_metode'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Jumlah Kopi (gr)</label>
                        <input type="text" name="jumlah_kopi" value="<?= htmlspecialchars($data['jumlah_kopi']) ?>">
                    </div>
                    <div style="flex:1; margin-right:10px;">
                        <label>Jumlah Air (ml)</label>
                        <input type="text" name="jumlah_air" value="<?= htmlspecialchars($data['jumlah_air']) ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Suhu (°C)</label>
                        <input type="text" name="suhu" value="<?= htmlspecialchars($data['suhu']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Grind Size</label>
                        <input type="text" name="ukuran_gilingan" value="<?= htmlspecialchars($data['ukuran_gilingan']) ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Waktu (s)</label>
                        <input type="number" name="waktu_ekstraksi" value="<?= $data['waktu_ekstraksi'] ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex:1; margin-right:10px;">
                        <label>Berat Minuman</label>
                        <input type="number" name="berat_minuman" value="<?= $data['berat_minuman'] ?>">
                    </div>
                    <div style="flex:1;">
                        <label>TDS</label>
                        <input type="text" name="tds" value="<?= $data['tds'] ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>

                <div class="form-group" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                    <label style="display:block; margin-bottom:10px; font-weight:bold;">Alat Digunakan:</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <?php while($a = mysqli_fetch_assoc($alats)): 
                            $isChecked = in_array($a['id_alat'], $selected_alat) ? 'checked' : '';
                        ?>
                            <label style="cursor: pointer;">
                                <input type="checkbox" name="alat[]" value="<?= $a['id_alat'] ?>" <?= $isChecked ?>> 
                                <?= htmlspecialchars($a['nama_alat']) ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Resep</button>
                    <a href="index.php" class="btn btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/Mobile/resep.js"></script>
<?php include("../../Component/bottom.php"); ?>