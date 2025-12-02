<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include("../../../Koneksi/koneksi.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';
$action_type = $_POST['action_type'] ?? '';
$base_url = "/SI-Ukopia/FrontOffice/auth/forgot-password.php";
if ($action_type == 'send_code') {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Email tidak ditemukan.');
        exit;
    }
    $reset_token = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $stmt_update = $conn->prepare("UPDATE akun_customer SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?");
    $stmt_update->bind_param("sss", $reset_token, $expires_at, $email);
    $stmt_update->execute();
    $stmt_update->close();
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rayaghaniyya1@gmail.com'; // Email Gmail kamu (dari kodemu)
        $mail->Password   = 'iehd xtvq hvzc mhox'; // 16 digit App Password (dari kodemu)
        $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee'); // Email Gmail kamu
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Kode Reset Password Ukopia Anda';
        $mail->Body    = "Kode verifikasi Anda adalah: 
                        <h1 style='font-size: 32px; letter-spacing: 5px; margin: 10px 0;'>
                            $reset_token
                        </h1>
                        Kode ini akan hangus dalam 10 menit.";
        $mail->send();
        $_SESSION['reset_email'] = $email;
        header('Location: ' . $base_url . '?view=code');
        exit;
    } catch (Exception $e) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Gagal mengirim email: ' . $mail->ErrorInfo);
        exit;
    }
}
if ($action_type == 'verify_code') {
    $reset_token = $_POST['reset_token'];
    $email = $_SESSION['reset_email'] ?? null; // Ambil email dari session
    if (empty($email)) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Sesi berakhir, silakan masukkan email Anda lagi.');
        exit;
    }
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ? AND reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->bind_param("ss", $email, $reset_token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $_SESSION['reset_token_verified'] = true;
        header('Location: ' . $base_url . '?view=reset');
        exit;
    } else {
        header('Location: ' . $base_url . '?view=code&status=error&message=Kode salah atau sudah kedaluwarsa.');
        exit;
    }
}
if ($action_type == 'reset_password') {
    if (!isset($_SESSION['reset_token_verified']) || !isset($_SESSION['reset_email'])) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Sesi tidak valid. Harap ulangi proses.');
        exit;
    }
    $password_new = $_POST['password_new'];
    $confirm_password_new = $_POST['confirm_password_new'];
    $email = $_SESSION['reset_email'];
    if ($password_new !== $confirm_password_new) {
        header('Location: ' . $base_url . '?view=reset&status=error&message=Password baru tidak cocok.');
        exit;
    }
    $hashed_password = password_hash($password_new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE akun_customer SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    $stmt->execute();
    $stmt->close();
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_token_verified']);
    header('Location: /SI-Ukopia/FrontOffice/auth/login.php?status=success&message=Password berhasil diubah. Silakan login.');
    exit;
}
header('Location: ' . $base_url);
exit;

