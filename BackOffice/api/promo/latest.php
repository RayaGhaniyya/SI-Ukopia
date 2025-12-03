<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json");

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host/SI-Ukopia/BackOffice/Mobile/Uploads/Promo/";

try {
    $sql = "SELECT gambar FROM promo ORDER BY created_at DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'has_promo' => true,
            'image_url' => $base_url . $row['gambar']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_promo' => false,
            'message' => 'Belum ada promo'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>