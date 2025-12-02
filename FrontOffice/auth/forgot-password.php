<?php
session_start();
include("../../Koneksi/koneksi.php"); // Path 2x ../
$view = $_GET['view'] ?? 'email';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Ukopia</title>
    <link rel="stylesheet" href="../assets/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper <?php
                                if ($view == 'code') echo 'is-code-view';
                                if ($view == 'reset') echo 'is-reset-view';
                                ?>">
        <div class="auth-left-panel">
            <div class="auth-slideshow">
                <div class="slide" style="background-image: url('../assets/img/Gallery-Homepage/foto 1.JPG');"></div>
                <div class="slide" style="background-image: url('../assets/img/Gallery-Homepage/foto 2.JPG');"></div>
            </div>
        </div>
        <div class="auth-right-panel">
            <div class="auth-form-container">
                <div class="auth-form-login">
                    <h2>Lupa Password</h2>
                    <p>Masukkan email Anda untuk menerima kode verifikasi.</p>
                    <form action="action/forgot_process.php" method="POST" class="auth-form">
                        <input type="hidden" name="action_type" value="send_code">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
                        </div>
                        <button type="submit" class="auth-btn">Kirim Kode</button>
                        <div class="auth-link">
                            <a href="login.php">Kembali ke Login</a>
                        </div>
                    </form>
                </div>
                <div class="auth-form-register">
                    <h2>Masukkan Kode</h2>
                    <p>Kode 6 digit telah dikirim ke email Anda.</p>
                    <form action="action/forgot_process.php" method="POST" class="auth-form">
                        <input type="hidden" name="action_type" value="verify_code">
                        <div class="form-group">
                            <label for="kode">Kode Verifikasi (6 Digit)</label>
                            <input type="text" id="kode" name="reset_token" placeholder="123456"
                                style="text-align: center; font-size: 1.2rem; letter-spacing: 5px;" required>
                        </div>
                        <button type="submit" class="auth-btn">Verifikasi</button>
                        <div class="auth-link">
                            <a href="forgot-password.php">Kembali</a>
                        </div>
                    </form>
                </div>
                <div class="auth-form-reset">
                    <h2>Atur Password Baru</h2>
                    <p>Masukkan password baru Anda.</p>
                    <form action="action/forgot_process.php" method="POST" class="auth-form" id="resetForm">
                        <input type="hidden" name="action_type" value="reset_password">
                        <div class="form-group">
                            <label for="password_new">Password Baru</label>
                            <div class="password-wrapper">
                                <input type="password" id="password_new" name="password_new" placeholder="Min. 8 karakter, 1 Huruf Besar" required>
                                <i class="fas fa-eye password-icon" id="togglePasswordNew"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password_new">Konfirmasi Password Baru</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password_new" name="confirm_password_new" placeholder="Ulangi password" required>
                                <i class="fas fa-eye password-icon" id="toggleConfirmNew"></i>
                            </div>
                        </div>
                        <button type="submit" class="auth-btn">Simpan Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/auth.js?v=<?php echo time(); ?>"></script>
</body>
</html>

