<?php
session_start();
include("../../Koneksi/koneksi.php");

// Proteksi Halaman
if (!isset($_SESSION['customer_uid'])) {
    header('Location: ../auth/login.php');
    exit;
}

$customer_uid = $_SESSION['customer_uid'];
$customer = null;

// Ambil Data Customer
$stmt = $conn->prepare("SELECT nama, username, email FROM akun_customer WHERE uid = ?");
$stmt->bind_param("i", $customer_uid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $customer = $result->fetch_assoc();
} else {
    session_destroy();
    header('Location: ../auth/login.php?status=error&message=Sesi tidak valid.');
    exit;
}
$stmt->close();
$conn->close();

include("../Component/Loader.php");
include("../Component/NavBar.php");
?>

<link rel="stylesheet" href="../assets/css/toast.css">
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
                <h2 class="profile-name-modern"><?php echo htmlspecialchars($customer['nama']); ?></h2>
                <p class="profile-username-modern">@<?php echo htmlspecialchars($customer['username']); ?></p>
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
                            <input type="text" class="form-control" id="namaLengkap" name="nama" value="<?php echo htmlspecialchars($customer['nama']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($customer['username']); ?>" disabled>
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
                        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#emailModal">Ganti</button>
                    </li>
                    <li class="list-group-item profile-list-item">
                        <div class="item-icon"><i class="fas fa-lock"></i></div>
                        <div class="item-content">
                            <h6>Password</h6>
                            <p>Ubah password Anda</p>
                        </div>
                        <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#passwordModal">Ganti</button>
                    </li>
                    <li class="list-group-item profile-list-item">
                        <div class="item-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="item-content">
                            <h6>Alamat</h6>
                            <p>Kelola alamat pengiriman Anda</p>
                        </div>
                        <a class="btn btn-outline-dark btn-sm" data-bs-toggle="collapse" href="#collapseAlamat">Kelola</a>
                    </li>
                    <li class="list-group-item profile-list-item">
                        <div class="item-icon" style="background-color: #fce8e8;"><i class="fas fa-sign-out-alt" style="color: #dc3545;"></i></div>
                        <div class="item-content">
                            <h6>Logout</h6>
                            <p>Keluar dari akun Anda dengan aman</p>
                        </div>
                        <button id="logoutBtn" class="btn btn-outline-danger btn-sm">Keluar</button>
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
                                <div class="col-md-6"><label class="form-label">Label Alamat</label><input type="text" class="form-control" name="label_alamat" placeholder="Rumah, Kantor" required></div>
                                <div class="col-md-6"><label class="form-label">Nama Penerima</label><input type="text" class="form-control" name="nama_penerima" required></div>
                                <div class="col-md-6"><label class="form-label">No. Telepon</label><input type="text" class="form-control" name="no_telepon" required></div>
                                <div class="col-md-6"><label class="form-label">Kode Pos</label><input type="text" class="form-control" name="kode_pos" required></div>
                                <div class="col-md-6"><label class="form-label">Kota / Kabupaten</label><input type="text" class="form-control" name="kota" required></div>
                                <div class="col-md-6"><label class="form-label">Provinsi</label><input type="text" class="form-control" name="provinsi" required></div>
                                <div class="col-12"><label class="form-label">Alamat Lengkap</label><textarea class="form-control" name="alamat_lengkap" rows="3" required></textarea></div>
                                <div class="col-12">
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_utama" value="1"><label class="form-check-label">Jadikan alamat utama</label></div>
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
                <form id="emailStep1Form">
                    <div class="mb-3">
                        <label class="form-label">Email Baru</label>
                        <input type="email" class="form-control" name="new_email" id="inputNewEmail" placeholder="nama@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="password" required>
                            <i class="fas fa-eye password-icon" onclick="togglePass(this)"></i>
                        </div>
                        <div class="form-text text-end">
                            <a href="../auth/forgot-password.php" class="text-decoration-none" style="font-size: 0.85rem;">Lupa Password?</a>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Kirim Kode Verifikasi</button>
                </form>

                <form id="emailStep2Form" style="display: none;">
                    <div class="alert alert-success" style="font-size: 0.9rem;">
                        Kode verifikasi telah dikirim ke <strong id="targetEmailDisplay"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Masukkan Kode 6 Digit</label>
                        <input type="text" class="form-control text-center" name="verification_code" style="letter-spacing: 5px; font-size: 1.2rem;" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Verifikasi & Simpan</button>
                    <button type="button" class="btn btn-link w-100 mt-2" id="btnBatalEmail">Ganti Email Lain</button>
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

                <form id="passStep1Form">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="old_password" id="oldPassword" required>
                            <i class="fas fa-eye password-icon" onclick="togglePass(this)"></i>
                        </div>
                        <div class="form-text text-end">
                            <a href="../auth/forgot-password.php" class="text-decoration-none" style="font-size: 0.85rem;">Lupa Password lama?</a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="new_password" id="newPasswordInput" placeholder="Min. 8 Karakter, Huruf Besar & Kecil" required>
                            <i class="fas fa-eye password-icon" onclick="togglePass(this)"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="confirm_new_password" id="confirmNewPasswordInput" required>
                            <i class="fas fa-eye password-icon" onclick="togglePass(this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">Kirim Kode Verifikasi</button>
                </form>

                <form id="passStep2Form" style="display: none;">
                    <div class="alert alert-success" style="font-size: 0.9rem;">
                        Kode verifikasi keamanan dikirim ke email Anda (<strong><?php echo htmlspecialchars($customer['email']); ?></strong>).
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Masukkan Kode 6 Digit</label>
                        <input type="text" class="form-control text-center" name="verification_code"
                            style="letter-spacing: 5px; font-size: 1.2rem;" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Verifikasi & Simpan Password</button>
                    <button type="button" class="btn btn-link w-100 mt-2" id="btnBatalPass">Batal</button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/toast.js"></script>
<script src="../assets/js/profile.js?v=<?php echo filemtime('../assets/js/profile.js'); ?>"></script>

<script>
    function togglePass(icon) {
        const input = icon.previousElementSibling;
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>