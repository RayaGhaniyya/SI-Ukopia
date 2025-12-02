<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
$id = intval($_POST['id'] ?? 0);
$UPLOAD_DIR = '../../Uploads/Promo/';
$row = $conn->query("SELECT gambar FROM promo WHERE id_promo = $id")->fetch_assoc();
if ($row && $conn->query("DELETE FROM promo WHERE id_promo = $id")) {
    if(file_exists($UPLOAD_DIR . $row['gambar'])) unlink($UPLOAD_DIR . $row['gambar']);
    echo json_encode(['success' => true, 'message' => 'Promo dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal hapus']);
}
?>
