<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$identifier = trim($data['identifier'] ?? ''); // Email atau Username
$password   = trim($data['password'] ?? '');

if (empty($identifier) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email/Username dan Password wajib diisi']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT uid, nama, email, username, password, is_verified FROM akun_customer WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            
            if ($user['is_verified'] == 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Akun belum aktif. Silakan cek email Anda.',
                    'is_verified' => false
                ]);
                exit;
            }

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
