<?php
session_start();
// [PATH] Sesuaikan path ke file koneksi Anda
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Gagal menghapus alamat.'
];

// 1. Validasi Sesi Login
if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}
$customer_uid = $_SESSION['customer_uid'];

// 2. Ambil ID Alamat dari POST
if (!isset($_POST['id_alamat']) || empty($_POST['id_alamat'])) {
    $response['message'] = 'ID Alamat tidak ditemukan.';
    echo json_encode($response);
    exit;
}
$id_alamat = $_POST['id_alamat'];

// 3. Eksekusi Hapus dengan Pengecekan Keamanan
try {
    // Ini adalah bagian penting:
    // Kita menghapus HANYA JIKA id_alamat DAN uid_customer cocok.
    // Ini mencegah user A menghapus alamat user B.
    $sql = "DELETE FROM alamat_customer 
            WHERE id_alamat = ? AND uid_customer = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_alamat, $customer_uid);
    $stmt->execute();

    // 4. Cek apakah ada baris yang terhapus
    if ($stmt->affected_rows > 0) {
        // Jika affected_rows > 0, berarti delete berhasil
        $response['success'] = true;
        $response['message'] = 'Alamat berhasil dihapus!';
    } else {
        // Jika 0, berarti alamat itu tidak ada ATAU bukan milik user ini
        $response['message'] = 'Gagal menghapus alamat. Alamat tidak ditemukan atau bukan milik Anda.';
    }

    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Error database: ' . $e->getMessage();
}

$conn->close();

// 5. Kembalikan respons
echo json_encode($response);
