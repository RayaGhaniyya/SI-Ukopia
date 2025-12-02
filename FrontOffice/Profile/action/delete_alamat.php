<?php
session_start();
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Gagal menghapus alamat.'
];

if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}
$customer_uid = $_SESSION['customer_uid'];

if (!isset($_POST['id_alamat']) || empty($_POST['id_alamat'])) {
    $response['message'] = 'ID Alamat tidak ditemukan.';
    echo json_encode($response);
    exit;
}
$id_alamat = $_POST['id_alamat'];

try {
    $sql = "DELETE FROM alamat_customer 
            WHERE id_alamat = ? AND uid_customer = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_alamat, $customer_uid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Alamat berhasil dihapus!';
    } else {
        $response['message'] = 'Gagal menghapus alamat. Alamat tidak ditemukan atau bukan milik Anda.';
    }

    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Error database: ' . $e->getMessage();
}

$conn->close();

echo json_encode($response);

