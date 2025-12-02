<?php
session_start();
include("../../../Koneksi/koneksi.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $base_url_login = "/SI-Ukopia/FrontOffice/auth/login.php"; // URL untuk error
    $nama = $_POST['nama'];
    $username = $_POST['username']; // [BARU] Ambil username
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($nama) || empty($username) || empty($email) || empty($password)) {
        header('Location: ' . $base_url_login . '?view=register&status=error&message=Semua field wajib diisi');
        exit;
    }
    $stmt_check = $conn->prepare("SELECT email FROM akun_customer WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header('Location: ' . $base_url_login . '?view=register&status=error&message=Email sudah terdaftar');
        exit;
    }
    $stmt_check->close();
    $stmt_check_user = $conn->prepare("SELECT username FROM akun_customer WHERE username = ?");
    $stmt_check_user->bind_param("s", $username);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();
    if ($result_check_user->num_rows > 0) {
        header('Location: ' . $base_url_login . '?view=register&status=error&message=Username sudah terpakai, coba yang lain');
        exit;
    }
    $stmt_check_user->close();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_code = bin2hex(random_bytes(16));
    $stmt_insert = $conn->prepare(
        "INSERT INTO akun_customer (nama, username, email, password, verification_code, is_verified) 
         VALUES (?, ?, ?, ?, ?, 0)"
    );
    $stmt_insert->bind_param("sssss", $nama, $username, $email, $hashed_password, $verification_code);
    if ($stmt_insert->execute()) {
        $_SESSION['verification_email'] = $email;
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rayaghaniyya1@gmail.com';
            $mail->Password   = 'iehd xtvq hvzc mhox';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee');
            $mail->addAddress($email, $nama);
            $mail->isHTML(true);
            $mail->Subject = 'Verifikasi Akun Ukopia Anda';
            $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/SI-Ukopia/FrontOffice/auth/verify.php?code=" . $verification_code;
            $mail->Body    = "Halo $nama,<br><br>
                            Terima kasih sudah mendaftar di Ukopia. <br>
                            Silakan klik link di bawah ini untuk mengaktifkan akun Anda:<br><br>
                            <a href='$verification_link' style='background: #111; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 8px;'>
                                Verifikasi Akun Saya
                            </a>
                            <br><br>
                            (Link: $verification_link)
                            <br><br>
                            Salam,<br>Tim Ukopia";
            $mail->send();
            header('Location: ../pending-verification.php');
            exit;
        } catch (Exception $e) {
            $conn->query("DELETE FROM akun_customer WHERE email = '$email'");
            header('Location: ' . $base_url_login . '?view=register&status=error&message=Gagal mengirim email verifikasi. Coba lagi.');
            exit;
        }
    } else {
        header('Location: ' . $base_url_login . '?view=register&status=error&message=Gagal mendaftarkan akun ke database.');
        exit;
    }
    $stmt_insert->close();
}
$conn->close();

