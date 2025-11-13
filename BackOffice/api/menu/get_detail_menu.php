<?php
header('Content-Type: application/json');
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil ID Menu (wajib)
$id_menu = isset($_GET['id_menu']) ? intval($_GET['id_menu']) : 0;
// Ambil ID User yang sedang login (opsional, untuk cek kepemilikan)
$current_uid = isset($_GET['uid_akun']) ? intval($_GET['uid_akun']) : 0;

if ($id_menu <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Menu tidak valid.']);
    die();
}

$response = [];
$reviews_list = [];

// Kueri: Ambil ulasan + nama customer + ID ulasan
// KITA TAMBAHKAN 'id_ulasan' DAN 'is_owner'
$sql = "SELECT 
            um.id_ulasan, -- ID unik ulasan (PENTING)
            ac.nama,
            um.rating,
            um.tanggal_waktu,
            um.komentar,
            -- Cek apakah ulasan ini milik user yang sedang login
            (um.uid_akun = ?) AS is_owner 
        FROM 
            ulasan_menu um
        JOIN 
            akun_customer ac ON um.uid_akun = ac.uid
        WHERE 
            um.id_menu = ?
        ORDER BY 
            um.tanggal_waktu DESC";

$stmt = $db->prepare($sql);
// 'i' = integer. Parameter pertama untuk (um.uid_akun = ?), parameter kedua untuk (um.id_menu = ?)
$stmt->bind_param("ii", $current_uid, $id_menu); 
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reviews_list[] = $row;
    }
}

$response['status'] = 'success';
$response['data_ulasan'] = $reviews_list;

// JSON_NUMERIC_CHECK akan mengubah 'is_owner' menjadi 0 atau 1
echo json_encode($response, JSON_NUMERIC_CHECK); 
$stmt->close();
$db->close();
?>