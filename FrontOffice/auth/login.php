<?php
session_start();
include("../../Koneksi/koneksi.php");

if (isset($_SESSION['customer_uid'])) {
    // Jika user iseng buka halaman login padahal sudah login
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        header('Location: ../Product-Checkout/index.php');
    } else {
        header('Location: ../HomePage/index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'login') {

    $login_identifier = $_POST['login_identifier'];
    $password = $_POST['password'];

    if (empty($login_identifier) || empty($password)) {
        header('Location: login.php?status=error&message=Email/Username dan Password wajib diisi');
        exit;
    } else {
        $stmt = $conn->prepare("SELECT uid, nama, password, is_verified FROM akun_customer WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $login_identifier, $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();

            if (password_verify($password, $customer['password'])) {

                if ($customer['is_verified'] == 0) {
                    $_SESSION['verification_email'] = $login_identifier;
                    header('Location: login.php?status=error&message=Akun belum aktif. Silakan cek email atau login ulang.');
                    exit;
                } else {
                    $_SESSION['customer_uid'] = $customer['uid'];
                    $_SESSION['customer_nama'] = $customer['nama'];

                    session_write_close();

                    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                        header('Location: ../Product-Checkout/index.php');
                    } else {
                        header('Location: ../HomePage/index.php');
                    }
                    exit;
                }
            } else {
                header('Location: login.php?status=error&message=Password salah.');
                exit;
            }
        } else {
            header('Location: login.php?status=error&message=Akun tidak ditemukan.');
            exit;
        }
    }
}
$conn->close();

$view_register = (isset($_GET['view']) && $_GET['view'] == 'register');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ukopia</title>

    <link rel="stylesheet" href="../assets/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="auth-wrapper <?php if ($view_register) echo 'is-register-view'; ?>">

        <div class="auth-form-container">

            <div class="auth-form-register">
                <h2>Buat Akun Baru</h2>
                <p>Daftar untuk menyimpan alamat & melacak pesanan.</p>

                <form action="action/register_process.php" method="POST" class="auth-form" id="registerForm">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" placeholder="Nama" required>
                    </div>
                    <div class="form-group">
                        <label for="username_reg">Username</label>
                        <input type="text" id="username_reg" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <label for="email_reg">Email</label>
                        <input type="email" id="email_reg" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="password_reg">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password_reg" name="password" placeholder="Min. 8 karakter, 1 Huruf Besar" required>
                            <i class="fas fa-eye password-icon" id="togglePasswordReg"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                            <i class="fas fa-eye password-icon" id="toggleConfirmPassword"></i>
                        </div>
                    </div>
                    <button type="submit" class="auth-btn">Daftar</button>
                    <div class="auth-link">
                        Sudah punya akun? <a href="#" id="toggleToLogin">Login di sini</a>
                    </div>
                </form>
            </div>

            <div class="auth-form-login">
                <h2>Selamat Datang Kembali</h2>
                <p>Silakan login untuk melanjutkan.</p>

                <form action="login.php" method="POST" class="auth-form">
                    <input type="hidden" name="form_type" value="login">

                    <div class="form-group">
                        <label for="login_identifier">Email atau Username</label>
                        <input type="text" id="login_identifier" name="login_identifier" placeholder="Email atau Username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                            <i class="fas fa-eye password-icon" id="togglePassword"></i>
                        </div>
                    </div>
                    <div class="form-options">
                        <a href="forgot-password.php" class="forgot-link">Lupa Password?</a>
                    </div>
                    <button type="submit" class="auth-btn">Login</button>
                    <div class="auth-link">
                        Belum punya akun? <a href="#" id="toggleToRegister">Daftar di sini</a>
                    </div>
                </form>
            </div>

        </div>

        <div class="auth-overlay-panel">
            <div class="auth-slideshow">
                <div class="slide" style="background-image: url('../assets/img/Gallery-Homepage/foto 1.JPG');"></div>
                <div class="slide" style="background-image: url('../assets/img/Gallery-Homepage/foto 2.JPG');"></div>
                <div class="slide" style="background-image: url('../assets/img/Gallery-Homepage/foto 3.JPG');"></div>
                <div class="slide" style="background-image: url('../assets/img/Gallery-Homepage/foto 4.JPG');"></div>
            </div>
        </div>

    </div>

    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/auth.js?v=<?php echo time(); ?>"></script>

</body>

</html>