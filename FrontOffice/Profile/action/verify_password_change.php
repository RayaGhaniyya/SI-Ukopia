<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid']) || !isset($_SESSION['temp_pass_hash']) || !isset($_SESSION['temp_pass_code'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Ulangi proses.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$input_code = $_POST['verification_code'] ?? '';
$session_code = $_SESSION['temp_pass_code'];
$new_hash = $_SESSION['temp_pass_hash'];

if ($input_code == $session_code) {
    $stmt = $conn->prepare("UPDATE akun_customer SET password = ? WHERE uid = ?");
    $stmt->bind_param("si", $new_hash, $uid);

    if ($stmt->execute()) {
        unset($_SESSION['temp_pass_hash']);
        unset($_SESSION['temp_pass_code']);

        echo json_encode(['success' => true, 'message' => 'Password berhasil diperbarui!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update database.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Kode verifikasi salah.']);
}
