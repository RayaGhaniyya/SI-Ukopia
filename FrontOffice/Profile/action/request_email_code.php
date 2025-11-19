<?php
session_start();
include("../../../Koneksi/koneksi.php");

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/PHPMailer/src/Exception.php';
require '../../../vendor/PHPMailer/src/PHPMailer.php';
require '../../../vendor/PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi habis.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$new_email = $_POST['new_email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($new_email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

// 1. Cek Password & Email Lama
$stmt = $conn->prepare("SELECT password, email FROM akun_customer WHERE uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Password salah.']);
    exit;
}

if ($new_email === $user['email']) {
    echo json_encode(['success' => false, 'message' => 'Email baru sama dengan yang lama.']);
    exit;
}

// 2. Cek Email Baru Unik
$stmt_check = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ?");
$stmt_check->bind_param("s", $new_email);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email sudah digunakan akun lain.']);
    exit;
}

// 3. Generate Kode
$code = rand(100000, 999999);
$_SESSION['temp_new_email'] = $new_email;
$_SESSION['temp_email_code'] = $code;

// 4. Kirim Email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rayaghaniyya1@gmail.com'; // Email Anda
    $mail->Password   = 'iehd xtvq hvzc mhox';     // App Password Anda
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee');
    $mail->addAddress($new_email);

    $mail->isHTML(true);
    $mail->Subject = 'Kode Verifikasi Ganti Email';
    $mail->Body    = "<h2>Ganti Email Ukopia</h2>
                      <p>Kode verifikasi Anda untuk mengganti email adalah:</p>
                      <h1 style='letter-spacing: 5px;'>$code</h1>
                      <p>Jangan berikan kode ini kepada siapa pun.</p>";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Kode terkirim ke email baru.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal kirim email: ' . $mail->ErrorInfo]);
}
