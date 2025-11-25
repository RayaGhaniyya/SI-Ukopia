<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

// 1. Ambil UID dari URL
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid <= 0) {
    echo "<script>alert('Customer tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

// 2. Ambil Data Customer (Untuk ditampilkan namanya)
$stmt = $conn->prepare("SELECT nama, no_telpon FROM akun_customer WHERE uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    echo "<script>alert('Data Customer hilang!'); window.location.href='index.php';</script>";
    exit;
}

// 3. Ambil Kategori Menu (Untuk Dropdown)
$kategori_res = mysqli_query($conn, "SELECT * FROM kategori_menu ORDER BY nama_kategori ASC");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-coffee"></i> Input Menu Customer</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="form-container" style="max-width: 600px; margin: 30px auto;">
            
            <div class="alert alert-info" style="text-align:center; margin-bottom:20px;">
                Sedang menginput untuk:<br>
                <strong style="font-size:18px;"><?= htmlspecialchars($customer['nama']) ?></strong><br>
                <small><?= htmlspecialchars($customer['no_telpon']) ?></small>
            </div>

            <form action="action/store.php" method="POST">
                <input type="hidden" name="uid_akun" value="<?= $uid ?>">

                <div class="form-group">
                    <label>Nama Menu <span style="color:red">*</span></label>
                    <input type="text" name="nama_menu" class="form-control" required 
                           placeholder="Contoh: V60 Gayo, Latte Art, dll">
                </div>

                <div class="form-group">
                    <label>Kategori Menu <span style="color:red">*</span></label>
                    <select name="id_kategori" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($kat = mysqli_fetch_assoc($kategori_res)): ?>
                            <option value="<?= $kat['id_kategori_menu'] ?>">
                                <?= $kat['nama_kategori'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Biji Kopi / Beans <span style="color:red">*</span></label>
                    <input type="text" name="biji_kopi" class="form-control" required 
                           placeholder="Contoh: Arabica Gayo, House Blend, dll">
                </div>

                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="text" value="<?= date('d F Y') ?>" class="form-control" readonly 
                           style="background-color:#eee;">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Simpan & Beri Akses Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../../Component/bottom.php"); ?>