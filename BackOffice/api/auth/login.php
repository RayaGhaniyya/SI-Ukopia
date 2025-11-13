<?php
// file: api/login.php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include file database
include_once '../../config/database.php';

// Inisialisasi koneksi
$database = new Database();
$db = $database->getConnection();

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"));

// Pastikan email dan password tidak kosong
if (!empty($data->email) && !empty($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // Query untuk mengambil data user berdasarkan email
    $query = "SELECT uid, nama, email, password FROM akun_customer WHERE email = ? LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $uid_db = $row['uid'];
        $nama_db = $row['nama'];
        $email_db = $row['email'];
        $password_db = $row['password'];

        if (password_verify($password, $password_db)) {
            http_response_code(200);
            $user_data = array(
                "uid" => $uid_db,
                "nama" => $nama_db,
                "email" => $email_db
            );
            echo json_encode(array(
                "message" => "Login berhasil.",
                "data" => $user_data
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login gagal. Password salah."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Login gagal. Email tidak ditemukan."));
    }
} else {
    // Jika data tidak lengkap
    http_response_code(400);
    echo json_encode(array("message" => "Login gagal. Data tidak lengkap."));
}

$db->close();
