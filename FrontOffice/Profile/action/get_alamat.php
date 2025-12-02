<?php
session_start();
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Gagal memuat alamat.',
    'alamat' => [] // array kosong untuk menampung data alamat
];

if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}

$customer_uid = $_SESSION['customer_uid'];

try {
    $sql = "SELECT id_alamat, label_alamat, nama_penerima, no_telepon, alamat_lengkap, kota, provinsi, kode_pos, is_utama 
            FROM alamat_customer 
            WHERE uid_customer = ? 
            ORDER BY is_utama DESC, id_alamat ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    $alamat_list = $result->fetch_all(MYSQLI_ASSOC);

    $response['success'] = true;
    $response['alamat'] = $alamat_list;

    if (count($alamat_list) === 0) {
        $response['message'] = 'Belum ada alamat tersimpan.';
    } else {
        $response['message'] = 'Alamat berhasil dimuat.';
    }

    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Error database: ' . $e->getMessage();
}

$conn->close();

echo json_encode($response);

