<?php
session_start();
// Path 2x ../ (dari auth/ -> FrontOffice/ -> SI-Ukopia/ -> Koneksi/)
include("../../Koneksi/koneksi.php");

$message = "";
$is_success = false;

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $verification_code = $_GET['code'];
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE verification_code = ?");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $stmt_update = $conn->prepare(
            "UPDATE akun_customer SET is_verified = 1, verification_code = NULL 
             WHERE verification_code = ?"
        );
        $stmt_update->bind_param("s", $verification_code);

        if ($stmt_update->execute()) {
            $message = "Verifikasi berhasil! Akun Anda sudah aktif. Silakan login.";
            $is_success = true;
        } else {
            $message = "Terjadi kesalahan. Gagal memverifikasi akun.";
        }
        $stmt_update->close();
    } else {
        $message = "Kode verifikasi tidak valid atau sudah kedaluwarsa.";
    }
    $stmt->close();
} else {
    $message = "Tidak ada kode verifikasi.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun - Ukopia</title>

    <link rel="stylesheet" href="../assets/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/toast.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="verify-wrapper">

        <div class="verification-status <?php echo $is_success ? 'is-success' : 'is-error'; ?>">

            <div class="verification-icon">
                <i class="fas <?php echo $is_success ? 'fa-check' : 'fa-times'; ?>"></i>
            </div>

            <h2><?php echo $is_success ? 'Verifikasi Berhasil!' : 'Verifikasi Gagal'; ?></h2>
            <p><?= $message ?></p>

            <a href="login.php" class="auth-btn" style="text-align: center; text-decoration: none; margin-top: 10px;">
                Kembali ke Login
            </a>
        </div>

    </div>

    <script src="../assets/js/toast.js"></script>
</body>

</html>