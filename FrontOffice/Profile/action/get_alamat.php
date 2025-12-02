<?php
session_start();
// [PATH] Sesuaikan path ke file koneksi Anda.
// Asumsinya file ini ada di 'Profile/action/' dan koneksi ada di 'Koneksi/'
include("../../../Koneksi/koneksi.php");

// Atur header agar JavaScript tahu ini adalah respons JSON
header('Content-Type: application/json');

// Siapkan array respons default
$response = [
    'success' => false,
    'message' => 'Gagal memuat alamat.',
    'alamat' => [] // array kosong untuk menampung data alamat
];

// 1. Pastikan customer login
if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}

$customer_uid = $_SESSION['customer_uid'];

try {
    // 2. Siapkan dan eksekusi query
    // (Diurutkan agar alamat 'Utama' selalu di paling atas)
    $sql = "SELECT id_alamat, label_alamat, nama_penerima, no_telepon, alamat_lengkap, kota, provinsi, kode_pos, is_utama 
            FROM alamat_customer 
            WHERE uid_customer = ? 
            ORDER BY is_utama DESC, id_alamat ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    // 3. Ambil semua data
    $alamat_list = $result->fetch_all(MYSQLI_ASSOC);

    // 4. Masukkan data ke respons
    $response['success'] = true;
    $response['alamat'] = $alamat_list;

    // Jika tidak ada alamat, 'alamat' akan jadi array kosong, 
    // dan JS akan menampilkan "Masih belum memiliki alamat."
    // (sesuai logika di profile_modern.js)
    if (count($alamat_list) === 0) {
        $response['message'] = 'Belum ada alamat tersimpan.';
    } else {
        $response['message'] = 'Alamat berhasil dimuat.';
    }

    $stmt->close();
} catch (Exception $e) {
    // Tangani jika ada error database
    $response['message'] = 'Error database: ' . $e->getMessage();
}

$conn->close();

// 5. Kembalikan respons sebagai JSON
echo json_encode($response);
