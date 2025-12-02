<?php
ob_start();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Sesi habis.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// 1. Validasi Input Kosong
if (empty($old_password) || empty($new_password)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Semua kolom wajib diisi.']);
    exit;
}

// 2. Validasi Kecocokan Password
if ($new_password !== $confirm_new_password) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Password baru dan konfirmasi tidak cocok.']);
    exit;
}

// 3. VALIDASI KEKUATAN PASSWORD (Sesuai Permintaan)
// Minimal 8 karakter
if (strlen($new_password) < 8) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter.']);
    exit;
}
// Harus ada Huruf Besar
if (!preg_match('/[A-Z]/', $new_password)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Password harus mengandung minimal 1 Huruf Besar (A-Z).']);
    exit;
}
// Harus ada Huruf Kecil
if (!preg_match('/[a-z]/', $new_password)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Password harus mengandung minimal 1 Huruf Kecil (a-z).']);
    exit;
}

try {
    include("../../../Koneksi/koneksi.php");

    // 4. Cek Password Lama
    $stmt = $conn->prepare("SELECT password, email, nama FROM akun_customer WHERE uid = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($old_password, $user['password'])) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Password lama salah.']);
        exit;
    }

    // 5. Generate Kode & Simpan HASH Password Baru di Session (Bukan password asli)
    $code = rand(100000, 999999);
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    $_SESSION['temp_pass_hash'] = $hashed_new_password; // Simpan calon password
    $_SESSION['temp_pass_code'] = $code;

    // 6. Kirim Email OTP
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rayaghaniyya1@gmail.com';
    $mail->Password   = 'pwbglchdrddglzzu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

    $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Security');
    $mail->addAddress($user['email']);

    $mail->isHTML(true);
    $mail->Subject = 'Kode Verifikasi Ganti Password';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2>Ganti Password</h2>
            <p>Halo {$user['nama']}, kami menerima permintaan ganti password.</p>
            <p>Gunakan kode berikut untuk memverifikasi:</p>
            <h1 style='letter-spacing: 5px; background: #eee; padding: 10px; display: inline-block;'>$code</h1>
            <p>Jika bukan Anda, abaikan email ini. Akun Anda aman.</p>
        </div>
    ";

    $mail->send();
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Kode verifikasi terkirim ke email Anda.']);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Gagal kirim email: ' . $e->getMessage()]);
}
