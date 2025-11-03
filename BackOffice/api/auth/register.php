<?php
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

// Ambil data JSON yang dikirim ke API
$data = json_decode(file_get_contents("php://input"));

// Pastikan data tidak kosong
if (
    !empty($data->nama) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    // Cek apakah email sudah terdaftar
    $check_email_query = "SELECT uid FROM akun_customer WHERE email = ?";
    $stmt_check = $db->prepare($check_email_query);
    $stmt_check->bind_param("s", $data->email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Jika email sudah ada, kirim response 409 Conflict
        http_response_code(409);
        echo json_encode(array("message" => "Registrasi gagal. Email sudah terdaftar."));
    } else {
        // Buat query untuk insert data
        $query = "INSERT INTO akun_customer (nama, email, password) VALUES (?, ?, ?)";
        
        $stmt = $db->prepare($query);

        // Membersihkan data (Sanitize)
        $nama = htmlspecialchars(strip_tags($data->nama));
        $email = htmlspecialchars(strip_tags($data->email));
        // HASH PASSWORD! Ini bagian paling penting.
        $password = password_hash($data->password, PASSWORD_BCRYPT);

        // Binding parameter
        $stmt->bind_param("sss", $nama, $email, $password);

        // Eksekusi query
        if ($stmt->execute()) {
            // Set response code - 201 created
            http_response_code(201);
            echo json_encode(array("message" => "Registrasi berhasil."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            echo json_encode(array("message" => "Gagal melakukan registrasi."));
        }
    }
    $stmt_check->close();
} else {
    // Jika data tidak lengkap, kirim response 400 bad request
    http_response_code(400);
    echo json_encode(array("message" => "Registrasi gagal. Data tidak lengkap."));
}

$db->close();
?>