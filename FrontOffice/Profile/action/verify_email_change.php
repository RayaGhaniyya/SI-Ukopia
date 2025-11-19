<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid']) || !isset($_SESSION['temp_new_email']) || !isset($_SESSION['temp_email_code'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Ulangi proses.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$input_code = $_POST['verification_code'] ?? '';
$session_code = $_SESSION['temp_email_code'];
$new_email = $_SESSION['temp_new_email'];

if ($input_code == $session_code) {
    $stmt = $conn->prepare("UPDATE akun_customer SET email = ? WHERE uid = ?");
    $stmt->bind_param("si", $new_email, $uid);
    
    if ($stmt->execute()) {
        unset($_SESSION['temp_new_email']);
        unset($_SESSION['temp_email_code']);
        
        echo json_encode(['success' => true, 'message' => 'Email berhasil diperbarui!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update database.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Kode verifikasi salah.']);
}
?>