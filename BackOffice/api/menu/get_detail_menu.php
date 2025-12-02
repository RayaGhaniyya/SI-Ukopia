<?php
header('Content-Type: application/json');
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_menu = isset($_GET['id_menu']) ? intval($_GET['id_menu']) : 0;
$current_uid = isset($_GET['uid_akun']) ? intval($_GET['uid_akun']) : 0;

if ($id_menu <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Menu tidak valid.']);
    die();
}

$response = [];
$reviews_list = [];

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

echo json_encode($response, JSON_NUMERIC_CHECK); 
$stmt->close();
$db->close();
?>
