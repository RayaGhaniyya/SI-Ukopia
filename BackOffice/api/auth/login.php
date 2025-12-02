<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 1. Ambil JSON dari Android
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$identifier = trim($data['identifier'] ?? ''); // Email atau Username
$password   = trim($data['password'] ?? '');

// 2. Validasi Input
if (empty($identifier) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email/Username dan Password wajib diisi']);
    exit;
}

try {
    // 3. Cek User
    $stmt = $conn->prepare("SELECT uid, nama, email, username, password, is_verified FROM akun_customer WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 4. Verifikasi Password
        if (password_verify($password, $user['password'])) {
            
            // 5. Cek Status Verifikasi Email
            if ($user['is_verified'] == 0) {
                // Beri tahu Android bahwa akun belum aktif
                echo json_encode([
                    'success' => false, 
                    'message' => 'Akun belum aktif. Silakan cek email Anda.',
                    'is_verified' => false
                ]);
                exit;
            }

            // 6. Login Sukses -> Kirim Data User
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'uid' => intval($user['uid']),
                    'nama' => $user['nama'],
                    'email' => $user['email'],
                    'username' => $user['username']
                ]
            ]);

        } else {
            echo json_encode(['success' => false, 'message' => 'Password salah']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Akun tidak ditemukan']);
    }
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>