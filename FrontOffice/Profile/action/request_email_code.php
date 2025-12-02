<?php
// Matikan output buffering biar kita bisa menangkap log error jika perlu
ob_start();
session_start();

// Namespace HARUS di paling atas
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// 1. Include Koneksi & Library
try {
    $koneksi_path = "../../../Koneksi/koneksi.php";
    if (!file_exists($koneksi_path)) {
        throw new Exception("File koneksi tidak ditemukan.");
    }
    include($koneksi_path);

    require '../../../vendor/PHPMailer/src/Exception.php';
    require '../../../vendor/PHPMailer/src/PHPMailer.php';
    require '../../../vendor/PHPMailer/src/SMTP.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
    exit;
}

// 2. Cek Sesi
if (!isset($_SESSION['customer_uid'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Sesi habis. Silakan login ulang.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$new_email = trim($_POST['new_email'] ?? ''); // Pakai trim() untuk hapus spasi di awal/akhir
$password = $_POST['password'] ?? '';

// 3. Validasi Input & FORMAT EMAIL
if (empty($new_email) || empty($password)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

// --- [VALIDASI BARU] Cek Format Email ---
if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid. Pastikan menggunakan @ dan nama domain.']);
    exit;
}
// -----------------------------------------

try {
    // 4. Cek Password Lama
    $stmt = $conn->prepare("SELECT password, email FROM akun_customer WHERE uid = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password'])) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Password saat ini salah.']);
        exit;
    }

    if ($new_email === $user['email']) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Email baru sama dengan email lama.']);
        exit;
    }

    // Cek apakah email baru sudah dipakai orang lain
    $stmt_check = $conn->prepare("SELECT uid FROM akun_customer WHERE email = ?");
    $stmt_check->bind_param("s", $new_email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Email tersebut sudah digunakan akun lain.']);
        exit;
    }
    $stmt_check->close();

    // 5. Generate Kode
    $code = rand(100000, 999999);
    $_SESSION['temp_new_email'] = $new_email;
    $_SESSION['temp_email_code'] = $code;

    // 6. Konfigurasi Email
    $mail = new PHPMailer(true);

    $smtp_debug = '';
    $mail->Debugoutput = function ($str, $level) use (&$smtp_debug) {
        $smtp_debug .= "$level: $str\n";
    };
    $mail->SMTPDebug = 2;

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rayaghaniyya1@gmail.com';
    $mail->Password   = 'pwbglchdrddglzzu'; // Password App Anda
    $mail->setFrom('rayaghaniyya1@gmail.com', 'Ukopia Coffee');

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->addAddress($new_email);

    $mail->isHTML(true);
    $mail->Subject = 'Kode Verifikasi Ganti Email';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2>Kode Verifikasi</h2>
            <p>Gunakan kode berikut untuk mengganti email Anda:</p>
            <h1 style='letter-spacing: 5px; background: #eee; padding: 10px; display: inline-block;'>$code</h1>
        </div>
    ";

    $mail->send();

    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Kode terkirim!']);
} catch (Exception $e) {
    ob_clean();
    $errorDetail = $mail->ErrorInfo . " | LOG: " . $smtp_debug;
    echo json_encode(['success' => false, 'message' => 'Gagal kirim email. Cek Console.', 'debug' => $errorDetail]);
}

$conn->close();
ob_end_flush();
