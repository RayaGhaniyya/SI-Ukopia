<?php
session_start();
// [PATH] Sesuaikan path ke file koneksi Anda
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Gagal memperbarui password.'
];

// 1. Validasi Sesi Login
if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}
$customer_uid = $_SESSION['customer_uid'];

// 2. Ambil data dari POST
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// 3. Validasi input
if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
    $response['message'] = 'Semua field password tidak boleh kosong.';
    echo json_encode($response);
    exit;
}

// Validasi ini juga ada di JS, tapi penting untuk ada di server
if ($new_password !== $confirm_new_password) {
    $response['message'] = 'Password baru dan konfirmasi tidak cocok.';
    echo json_encode($response);
    exit;
}

if (strlen($new_password) < 6) { // Atur minimal panjang password
    $response['message'] = 'Password baru terlalu pendek (minimal 6 karakter).';
    echo json_encode($response);
    exit;
}

try {
    // 4. Verifikasi Password Lama
    $sql_pass = "SELECT password FROM akun_customer WHERE uid = ?";
    $stmt_pass = $conn->prepare($sql_pass);
    $stmt_pass->bind_param("i", $customer_uid);
    $stmt_pass->execute();
    $result_pass = $stmt_pass->get_result();

    if ($result_pass->num_rows === 0) {
        $response['message'] = 'Data customer tidak ditemukan.';
        echo json_encode($response);
        exit;
    }

    $customer_data = $result_pass->fetch_assoc();
    $hashed_password = $customer_data['password'];
    $stmt_pass->close();

    // Cek password lama
    if (!password_verify($old_password, $hashed_password)) {
        $response['message'] = 'Password lama yang Anda masukkan salah.';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // 5. Buat Hash untuk Password Baru
    // Gunakan algoritma default yang aman (PASSWORD_BCRYPT)
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // 6. Lakukan UPDATE Password Baru
    $sql_update = "UPDATE akun_customer SET password = ? WHERE uid = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_hashed_password, $customer_uid);

    if ($stmt_update->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password berhasil diperbarui!';
    } else {
        $response['message'] = 'Gagal menyimpan password baru ke database.';
    }
    $stmt_update->close();
} catch (Exception $e) {
    $response['message'] = 'Error database: ' . $e->getMessage();
}

$conn->close();

// 7. Kembalikan respons
echo json_encode($response);
