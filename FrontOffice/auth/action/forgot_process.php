<?php
session_start();

// === TAMBAHAN UNTUK FIX TIMEZONE ===
// Atur zona waktu PHP agar SAMA dengan zona waktu database (WIB/Jakarta)
date_default_timezone_set('Asia/Jakarta');
// ===================================

// Path 3x ../ (dari action/ -> auth/ -> FrontOffice/ -> SI-Ukopia/ -> Koneksi/)
include("../../../Koneksi/koneksi.php");

// (1) Include file-file PHPMailer
// Path 3x ../ (dari action/ -> auth/ -> FrontOffice/ -> SI-Ukopia/ -> vendor/)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';

// Tentukan jenis aksi (kirim kode, verifikasi kode, atau reset password)
$action_type = $_POST['action_type'] ?? '';
$base_url = "/SI-Ukopia/FrontOffice/auth/forgot-password.php";

// ===========================================
// AKSI 1: KIRIM KODE VERIFIKASI
// ===========================================
if ($action_type == 'send_code') {
    $email = $_POST['email'];

    // Cek email ada di DB
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Email tidak ditemukan.');
        exit;
    }

    // Buat kode reset (6 digit angka)
    $reset_token = rand(100000, 999999);

    // Waktu kedaluwarsa (10 menit dari sekarang - MENGGUNAKAN TIMEZONE YG BENAR)
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Simpan token ke database (di kolom yang sudah kita buat)
    $stmt_update = $conn->prepare("UPDATE akun_customer SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?");
    $stmt_update->bind_param("sss", $reset_token, $expires_at, $email);
    $stmt_update->execute();
    $stmt_update->close();

    // Kirim email (PHPMailer)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // VVVVV--- (KONFIGURASI EMAIL) GANTI INI ---VVVVV
        $mail->Username   = 'rayaghaniyya1@gmail.com'; // Email Gmail kamu (dari kodemu)
        $mail->Password   = 'iehd xtvq hvzc mhox'; // 16 digit App Password (dari kodemu)
        $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee'); // Email Gmail kamu
        // ^^^^^--- GANTI DI ATAS ---^^^^^

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

        // Simpan email di session (agar tahu siapa yg mau reset di langkah 2)
        $_SESSION['reset_email'] = $email;

        // Redirect ke slide 2 (Masukkan Kode)
        header('Location: ' . $base_url . '?view=code');
        exit;
    } catch (Exception $e) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Gagal mengirim email: ' . $mail->ErrorInfo);
        exit;
    }
}

// ===========================================
// AKSI 2: VERIFIKASI KODE
// ===========================================
if ($action_type == 'verify_code') {
    $reset_token = $_POST['reset_token'];
    $email = $_SESSION['reset_email'] ?? null; // Ambil email dari session

    if (empty($email)) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Sesi berakhir, silakan masukkan email Anda lagi.');
        exit;
    }

    // Cek kode dan waktu kedaluwarsa
    // (Query ini sekarang akan membandingkan waktu WIB vs WIB, jadi akan benar)
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ? AND reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->bind_param("ss", $email, $reset_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Kode benar dan belum hangus
        // Tandai bahwa kode sudah benar
        $_SESSION['reset_token_verified'] = true;
        // Redirect ke slide 3 (Reset Password)
        header('Location: ' . $base_url . '?view=reset');
        exit;
    } else {
        // Kode salah atau sudah hangus
        header('Location: ' . $base_url . '?view=code&status=error&message=Kode salah atau sudah kedaluwarsa.');
        exit;
    }
}

// ===========================================
// AKSI 3: SIMPAN PASSWORD BARU
// ===========================================
if ($action_type == 'reset_password') {

    // Cek apakah user sudah lolos verifikasi kode
    if (!isset($_SESSION['reset_token_verified']) || !isset($_SESSION['reset_email'])) {
        header('Location: ' . $base_url . '?view=email&status=error&message=Sesi tidak valid. Harap ulangi proses.');
        exit;
    }

    $password_new = $_POST['password_new'];
    $confirm_password_new = $_POST['confirm_password_new'];
    $email = $_SESSION['reset_email'];

    // (Validasi JS-side sudah ada, tapi kita cek lagi di server)
    if ($password_new !== $confirm_password_new) {
        header('Location: ' . $base_url . '?view=reset&status=error&message=Password baru tidak cocok.');
        exit;
    }
    // (Validasi 8 karakter, A-Z, a-z juga sudah ada di JS)

    // Hash password baru
    $hashed_password = password_hash($password_new, PASSWORD_DEFAULT);

    // Update password di DB dan hapus token
    $stmt = $conn->prepare("UPDATE akun_customer SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    $stmt->execute();
    $stmt->close();

    // Hapus session
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_token_verified']);

    // Kirim ke halaman Login dengan pesan sukses
    header('Location: /SI-Ukopia/FrontOffice/auth/login.php?status=success&message=Password berhasil diubah. Silakan login.');
    exit;
}

// Jika tidak ada action_type yang cocok
header('Location: ' . $base_url);
exit;
