<?php
// [BARU] Mulai session dan include koneksi
session_start();
include("../../Koneksi/koneksi.php");

// [BARU] Proteksi Halaman: Cek jika customer sudah login
if (!isset($_SESSION['customer_uid'])) {
    // Jika belum, tendang ke halaman login
    header('Location: ../auth/login.php');
    exit;
}

// [BARU] Ambil data customer dari DB untuk ditampilkan
$customer_uid = $_SESSION['customer_uid'];
$customer = null;

$stmt = $conn->prepare("SELECT nama, username, email FROM akun_customer WHERE uid = ?");
$stmt->bind_param("i", $customer_uid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $customer = $result->fetch_assoc();
} else {
    // Error aneh, user login tapi data tidak ada? Hancurkan session & redirect.
    session_destroy();
    header('Location: ../auth/login.php?status=error&message=Sesi tidak valid.');
    exit;
}
$stmt->close();
$conn->close(); // Tutup koneksi (karena kita hanya 'read' di file ini)

// Include komponen UI
include("../Component/Loader.php");
// NavBar sekarang akan membaca session yang sudah dimulai
include("../Component/NavBar.php");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="../assets/css/profile.css?v=<?php echo filemtime('../assets/css/profile.css'); ?>">
<link rel="stylesheet" href="../assets/css/loader.css">
<script src="../assets/js/loader.js"></script>


<div class="profile-body">

    <div class="profile-title-header">
        <div class="container">
            <h1 class="profile-title">Your Profile</h1>

            <a href="../HomePage/index.php" class="btn-back-home">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <div class="container profile-content-container">

        <div class="profile-card-modern">

            <div class="card-header-modern">
                <div class="profile-avatar-modern">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="profile-name-modern">
                    <?php echo htmlspecialchars($customer['nama']); ?>
                </h2>
                <p class="profile-username-modern">
                    @<?php echo htmlspecialchars($customer['username']); ?>
                </p>
            </div>

            <div class="card-section">
                <form id="profileForm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title">Data Diri</h5>
                        <button type="button" id="editProfileBtn" class="btn btn-dark btn-sm">Edit</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="namaLengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="namaLengkap" name="nama"
                                value="<?php echo htmlspecialchars($customer['nama']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?php echo htmlspecialchars($customer['username']); ?>" disabled>
                        </div>
                    </div>
                </form>
            </div>

            <hr class="section-divider">

            <div class="card-section">
                <h5 class="section-title mb-3">Pengaturan Akun & Keamanan</h5>

                <ul class="list-group list-group-flush">

                    <li class="list-group-item profile-list-item">
                        <div class="item-icon"><i class="fas fa-envelope"></i></div>
                        <div class="item-content">
                            <h6>Email</h6>
                            <p><?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#emailModal">
                            Ganti
                        </button>
                    </li>

                    <li class="list-group-item profile-list-item">
                        <div class="item-icon"><i class="fas fa-lock"></i></div>
                        <div class="item-content">
                            <h6>Password</h6>
                            <p>Ubah password Anda</p>
                        </div>
                        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            Ganti
                        </button>
                    </li>

                    <li class="list-group-item profile-list-item">
                        <div class="item-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="item-content">
                            <h6>Alamat</h6>
                            <p>Kelola alamat pengiriman Anda</p>
                        </div>
                        <a class="btn btn-outline-dark btn-sm" data-bs-toggle="collapse" href="#collapseAlamat" role="button" aria-expanded="false" aria-controls="collapseAlamat">
                            Kelola
                        </a>
                    </li>

                    <li class="list-group-item profile-list-item">
                        <div class="item-icon" style="background-color: #fce8e8;"><i class="fas fa-sign-out-alt" style="color: #dc3545;"></i></div>
                        <div class="item-content">
                            <h6>Logout</h6>
                            <p>Keluar dari akun Anda dengan aman</p>
                        </div>
                        <a href="action/logout.php" class="btn btn-outline-danger btn-sm" onclick="return confirm('Anda yakin ingin keluar?');">
                            Keluar
                        </a>
                    </li>
                </ul>

                <div class="collapse" id="collapseAlamat">
                    <div class="address-content-wrapper">

                        <h5 class="section-title mt-4 mb-3">Alamat Tersimpan</h5>
                        <div class="address-placeholder text-center text-muted" id="daftarAlamatContainer">
                            <p>Memuat alamat...</p>
                        </div>

                        <hr class="my-4">

                        <h5 class="section-title mb-3">Tambah Alamat Baru</h5>
                        <form id="alamatChangeForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="labelAlamat" class="form-label">Label Alamat</label>
                                    <input type="text" class="form-control" id="labelAlamat" name="label_alamat"
                                        placeholder="Contoh: Rumah, Kantor" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="namaPenerima" class="form-label">Nama Penerima</label>
                                    <input type="text" class="form-control" id="namaPenerima" name="nama_penerima" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="noTelepon" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="noTelepon" name="no_telepon" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="kodePos" class="form-label">Kode Pos</label>
                                    <input type="text" class="form-control" id="kodePos" name="kode_pos" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="kota" class="form-label">Kota / Kabupaten</label>
                                    <input type="text" class="form-control" id="kota" name="kota" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="provinsi" class="form-label">Provinsi</label>
                                    <input type="text" class="form-control" id="provinsi" name="provinsi" required>
                                </div>
                                <div class="col-12">
                                    <label for="alamatLengkap" class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" id="alamatLengkap" name="alamat_lengkap" rows="3"
                                        placeholder="Nama jalan, nomor rumah, RT/RW..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isUtama" name="is_utama" value="1">
                                        <label class="form-check-label" for="isUtama">
                                            Jadikan alamat utama
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-dark w-100 mt-4">Simpan Alamat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile-card-modern">
            <div class="card-section">
                <h5 class="section-title mb-3">Riwayat Pesanan</h5>
                <div class="history-placeholder text-center text-muted">
                    <p>Belum ada riwayat pesanan.</p>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ganti Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="emailChangeForm">
                    <div class="mb-3">
                        <label for="newEmail" class="form-label">Email Baru</label>
                        <input type="email" class="form-control" id="newEmail" name="new_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassEmail" class="form-label">Password Anda</label>
                        <input type="password" class="form-control" id="confirmPassEmail" name="password"
                            placeholder="Masukkan password untuk konfirmasi" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Simpan Email Baru</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ganti Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="passwordChangeForm">
                    <div class="mb-3">
                        <label for="oldPassword" class="form-label">Password Lama</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="oldPassword" name="old_password" required>
                            <i class="fas fa-eye password-icon" id="toggleOldPassword"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <i class="fas fa-eye password-icon" id="toggleNewPassword"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmNewPassword" class="form-label">Konfirmasi Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password" required>
                            <i class="fas fa-eye password-icon" id="toggleConfirmNewPassword"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Simpan Password Baru</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="../assets/js/profile.js?v=<?php echo filemtime('../assets/js/profile.js'); ?>"></script>