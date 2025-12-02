<?php
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


$nama     = trim($data['nama'] ?? '');
$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($nama) || empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom wajib diisi (Nama, Username, Email, Password)']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT uid FROM akun_customer WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username sudah terpakai']);
        exit;
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_code = bin2hex(random_bytes(16));

    $stmt_insert = $conn->prepare("INSERT INTO akun_customer (nama, username, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt_insert->bind_param("sssss", $nama, $username, $email, $hashed_password, $verification_code);

    if ($stmt_insert->execute()) {
        
        $mail = new PHPMailer(true);
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
        $mail->Body    = "Halo $nama,<br>Silakan klik link ini: <a href='$verification_link'>Verifikasi Akun</a>";

        $mail->send();

        echo json_encode([
            'success' => true, 
            'message' => 'Registrasi berhasil. Cek email.'
        ]);
    } else {
        throw new Exception("Gagal insert database");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
