<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json");

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host/si-ukopia/BackOffice/List_Data/Uploads/Metode/";

try {
    $result = $conn->query("SELECT * FROM metode ORDER BY nama_metode ASC");
    
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = [
            'id_metode' => intval($row['id_metode']),
            'nama_metode' => $row['nama_metode'],
            'gambar' => !empty($row['gambar_metode']) ? $base_url . $row['gambar_metode'] : $base_url . 'default.svg'
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
