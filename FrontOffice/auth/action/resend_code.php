<?php
session_start();
include("../../../Koneksi/koneksi.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';

if (!isset($_SESSION['verification_email'])) {
    header('Location: ../login.php');
    exit;
}
$email = $_SESSION['verification_email'];

$base_url_pending = "/SI-Ukopia/FrontOffice/auth/pending-verification.php";
$base_url_login = "/SI-Ukopia/FrontOffice/auth/login.php";

try {
    $stmt_check = $conn->prepare("SELECT nama, is_verified FROM akun_customer WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception("Email tidak ditemukan.");
    }

    $customer = $result_check->fetch_assoc();
    $nama = $customer['nama'];

    if ($customer['is_verified'] == 1) {
        unset($_SESSION['verification_email']);
        header('Location: ' . $base_url_login . '?status=success&message=Akun Anda sudah aktif. Silakan login.');
        exit;
    }
    $stmt_check->close();

    $new_verification_code = bin2hex(random_bytes(16));

    $stmt_update = $conn->prepare("UPDATE akun_customer SET verification_code = ? WHERE email = ?");
    $stmt_update->bind_param("ss", $new_verification_code, $email);
    $stmt_update->execute();
    $stmt_update->close();

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rayaghaniyya1@gmail.com'; // (Ini dari kodemu)
    $mail->Password   = 'iehd xtvq hvzc mhox'; // (Ini dari kodemu)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee'); // (Ini dari kodemu)
    $mail->addAddress($email, $nama);
    $mail->isHTML(true);
    $mail->Subject = 'Kode Verifikasi Akun Ukopia (Kirim Ulang)';

    $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/SI-Ukopia/FrontOffice/auth/verify.php?code=" . $new_verification_code;

    $mail->Body    = "Halo $nama,<br><br>
                    Ini adalah link verifikasi baru Anda. <br>
                    Silakan klik link di bawah ini untuk mengaktifkan akun Anda:<br><br>
                    <a href='$verification_link' style='background: #111; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 8px;'>
                        Verifikasi Akun Saya
                    </a>
                    <br><br>
                    (Link: $verification_link)
                    <br><br>
                    Salam,<br>Tim Ukopia";

    $mail->send();

    header('Location: ' . $base_url_pending . '?status=success&message=Email verifikasi baru telah terkirim!');
    exit;
} catch (Exception $e) {
    header('Location: ' . $base_url_pending . '?status=error&message=' . $e->getMessage());
    exit;
}

$conn->close();

