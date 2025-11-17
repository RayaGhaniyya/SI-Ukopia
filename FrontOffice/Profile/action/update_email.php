<?php
session_start();
// [PATH] Sesuaikan path ke file koneksi Anda
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Gagal memperbarui email.'
];

// 1. Validasi Sesi Login
if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}
$customer_uid = $_SESSION['customer_uid'];

// 2. Ambil data dari POST
$new_email = $_POST['new_email'] ?? '';
$password = $_POST['password'] ?? '';

// 3. Validasi input
if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Format email baru tidak valid.';
    echo json_encode($response);
    exit;
}
if (empty($password)) {
    $response['message'] = 'Password konfirmasi tidak boleh kosong.';
    echo json_encode($response);
    exit;
}

try {
    // 4. Verifikasi Password
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

    // Cek password
    if (!password_verify($password, $hashed_password)) {
        $response['message'] = 'Password yang Anda masukkan salah.';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // 5. Cek Keunikan Email Baru
    // (Kita tidak perlu mengecek uid != ?, karena jika emailnya sama
    //  dengan emailnya sendiri, itu tidak masalah. Kita hanya
    //  perlu cek apakah email itu sudah dipakai orang lain)
    $sql_check = "SELECT uid FROM akun_customer WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $new_email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Cek apakah email itu milik kita sendiri atau bukan
        $existing_user = $result_check->fetch_assoc();
        if ($existing_user['uid'] != $customer_uid) {
            $response['message'] = 'Email tersebut sudah terdaftar untuk akun lain.';
            echo json_encode($response);
            $stmt_check->close();
            $conn->close();
            exit;
        }
        // Jika email itu milik kita sendiri, tidak perlu update, 
        // tapi kita anggap sukses saja.
    }
    $stmt_check->close();

    // 6. Lakukan UPDATE Email
    $sql_update = "UPDATE akun_customer SET email = ? WHERE uid = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_email, $customer_uid);

    if ($stmt_update->execute()) {
        $response['success'] = true;
        $response['message'] = 'Email berhasil diperbarui! Halaman akan dimuat ulang.';
    } else {
        $response['message'] = 'Gagal menyimpan email baru ke database.';
    }
    $stmt_update->close();
} catch (Exception $e) {
    $response['message'] = 'Error database: ' . $e->getMessage();
}

$conn->close();

// 7. Kembalikan respons
echo json_encode($response);
