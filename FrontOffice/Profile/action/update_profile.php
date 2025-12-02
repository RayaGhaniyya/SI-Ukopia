<?php
session_start();
// [PATH] Sesuaikan path ke file koneksi Anda
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Gagal memperbarui profil.'
];

// 1. Validasi Sesi Login
if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}
$customer_uid = $_SESSION['customer_uid'];

// 2. Ambil data dari POST
$nama = $_POST['nama'] ?? '';
$username = $_POST['username'] ?? '';

// 3. Validasi input
if (empty($nama) || empty($username)) {
    $response['message'] = 'Nama dan Username tidak boleh kosong.';
    echo json_encode($response);
    exit;
}

// 4. Validasi Keunikan Username
// Cek apakah ada user LAIN (uid != $customer_uid) 
// yang sudah menggunakan username tersebut.
try {
    $sql_check = "SELECT uid FROM akun_customer WHERE username = ? AND uid != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $username, $customer_uid);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika ada (num_rows > 0), berarti username sudah dipakai
        $response['message'] = 'Username "' . htmlspecialchars($username) . '" sudah digunakan oleh orang lain.';
        echo json_encode($response);
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();

    // 5. Jika username aman, lakukan UPDATE
    $sql_update = "UPDATE akun_customer SET nama = ?, username = ? WHERE uid = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $nama, $username, $customer_uid);

    if ($stmt_update->execute()) {
        $response['success'] = true;
        $response['message'] = 'Profil berhasil diperbarui!';
    } else {
        $response['message'] = 'Terjadi kesalahan saat menyimpan data.';
    }
    
    $stmt_update->close();

} catch (Exception $e) {
    // Tangani jika ada error duplikat (meskipun sudah dicek, 
    // ini untuk 'catch' jika ada race condition)
    if ($conn->errno == 1062) { // Error 'Duplicate entry'
        $response['message'] = 'Username tersebut sudah digunakan.';
    } else {
        $response['message'] = 'Error database: ' . $e->getMessage();
    }
}

$conn->close();

// 6. Kembalikan respons
echo json_encode($response);
?>