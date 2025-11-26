<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi habis.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$id_alamat = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_alamat == 0) {
    echo json_encode(['success' => false, 'message' => 'ID Alamat tidak valid.']);
    exit;
}

// Ambil data alamat spesifik milik user ini
$stmt = $conn->prepare("SELECT * FROM alamat_customer WHERE id_alamat = ? AND uid_customer = ?");
$stmt->bind_param("ii", $id_alamat, $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Alamat tidak ditemukan.']);
}

$stmt->close();
$conn->close();
?>