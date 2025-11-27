<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
if ($uid <= 0) { echo "<script>location.href='index.php';</script>"; exit; }

$stmt = $conn->prepare("SELECT nama FROM akun_customer WHERE uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// [PENTING] Ambil juga kolom 'biji' dari kategori_menu
$kategori_res = mysqli_query($conn, "SELECT * FROM kategori_menu ORDER BY nama_kategori ASC");
?>

<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-coffee"></i> Input Menu Customer</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="form-container" style="max-width: 600px; margin: 30px auto;">
            <div class="alert alert-info" style="text-align:center;">
                Input untuk: <strong><?= htmlspecialchars($customer['nama']) ?></strong>
            </div>

            <form action="action/store.php" method="POST">
                <input type="hidden" name="uid_akun" value="<?= $uid ?>">

                <div class="form-group">
                    <label>Kategori Menu <span style="color:red">*</span></label>
                    <select name="id_kategori" id="selectKategori" class="form-control" required onchange="loadMenu(this.value)">
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($kat = mysqli_fetch_assoc($kategori_res)): ?>
                            <option value="<?= $kat['id_kategori_menu'] ?>" data-biji="<?= $kat['biji'] ?>">
                                <?= $kat['nama_kategori'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nama Menu <span style="color:red">*</span></label>
                    <select name="id_menu" id="selectMenu" class="form-control" required disabled>
                        <option value="">-- Pilih Kategori Dulu --</option>
                    </select>
                </div>

                <div class="form-group" id="groupBijiKopi" style="display:none;">
                    <label>Jenis Biji Kopi / Beans</label>
                    <input type="text" name="biji_kopi" id="inputBijiKopi" class="form-control" placeholder="Contoh: Arabica Gayo">
                </div>

                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="text" value="<?= date('d F Y') ?>" class="form-control" readonly style="background-color:#eee;">
                </div>

                <div class="form-actions"><button type="submit" class="btn btn-primary btn-block">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function loadMenu(idKategori) {
    const selectMenu = document.getElementById('selectMenu');
    const groupBijiKopi = document.getElementById('groupBijiKopi');
    const inputBijiKopi = document.getElementById('inputBijiKopi');
    
    const selectKategori = document.getElementById('selectKategori');
    const selectedOption = selectKategori.options[selectKategori.selectedIndex];
    
    // [LOGIKA BARU] Cek atribut data-biji (1 = butuh biji, 0 = tidak)
    const butuhBiji = selectedOption.getAttribute('data-biji');

    if (butuhBiji == '1') {
        groupBijiKopi.style.display = 'block';
        inputBijiKopi.required = true; 
    } else {
        groupBijiKopi.style.display = 'none';
        inputBijiKopi.required = false;
        inputBijiKopi.value = ''; 
    }

    selectMenu.innerHTML = '<option value="">Loading...</option>';
    selectMenu.disabled = true;

    if (idKategori) {
        fetch('get_menu.php?id_kategori=' + idKategori)
            .then(response => response.json())
            .then(data => {
                let html = '<option value="">-- Pilih Menu --</option>';
                data.forEach(item => {
                    html += `<option value="${item.id_menu}">${item.nama_menu}</option>`;
                });
                selectMenu.innerHTML = html;
                selectMenu.disabled = false;
            })
            .catch(error => { console.error(error); selectMenu.innerHTML = '<option>Gagal</option>'; });
    } else {
        selectMenu.innerHTML = '<option value="">-- Pilih Kategori Dulu --</option>';
    }
}
</script>
<?php include("../../Component/bottom.php"); ?>