<?php
date_default_timezone_set('Asia/Jakarta');
include("../../../Koneksi/koneksi.php");

require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$json = file_get_contents("php://input");
$data = json_decode($json, true);
$action = $data['action'] ?? '';

// --- ACTION 1: KIRIM KODE OTP ---
if ($action == 'send_code') {
    $email = trim($data['email'] ?? '');
    
    // Cek Email
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan']);
        exit;
    }

    $reset_token = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Simpan Token ke DB
    $stmt_upd = $conn->prepare("UPDATE akun_customer SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?");
    $stmt_upd->bind_param("sss", $reset_token, $expires_at, $email);
    $stmt_upd->execute();

    // Kirim Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // GANTI DENGAN EMAIL ANDA
        $mail->Username   = 'rayaghaniyya1@gmail.com'; 
        $mail->Password   = 'iehd xtvq hvzc mhox'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Kode Reset Password Ukopia';
        $mail->Body    = "Kode verifikasi Anda adalah: <b>$reset_token</b>";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Kode terkirim ke email']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal kirim email']);
    }
}

// --- ACTION 2: VERIFIKASI KODE ---
else if ($action == 'verify_code') {
    $email = trim($data['email'] ?? '');
    $code  = trim($data['code'] ?? '');

    // Cek Kode & Expired Time
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ? AND reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Kode valid']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode salah atau kedaluwarsa']);
    }
}

// --- ACTION 3: RESET PASSWORD ---
else if ($action == 'reset_password') {
    $email = trim($data['email'] ?? '');
    $code  = trim($data['code'] ?? ''); // Kita butuh kode lagi untuk validasi terakhir
    $new_pass = trim($data['new_password'] ?? '');

    if (empty($new_pass) || strlen($new_pass) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter']);
        exit;
    }

    $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

    // Update Password & Hapus Token (Hanya jika kode & email cocok)
    $stmt = $conn->prepare("UPDATE akun_customer SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE email = ? AND reset_token = ?");
    $stmt->bind_param("sss", $hashed_password, $email, $code);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Password berhasil diubah']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah password. Sesi mungkin kedaluwarsa.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}
?>