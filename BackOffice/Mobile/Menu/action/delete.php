<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Menu tidak valid!']);
    exit;
}
$UPLOAD_DIR = '../../Uploads/Menu/';
try {
    $stmt_check = $conn->prepare("SELECT id_menu FROM menu WHERE id_menu = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        throw new Exception("Data menu tidak ditemukan!");
    }
    $stmt_check->close();
    $stmt_image = $conn->prepare("SELECT gambar_url FROM menu WHERE id_menu = ?");
    $stmt_image->bind_param("i", $id);
    $stmt_image->execute();
    $result_image = $stmt_image->get_result();
    $imageUrl = null;
    if ($row = $result_image->fetch_assoc()) {
        $imageUrl = $row['gambar_url'];
    }
    $stmt_image->close();
    $stmt_delete = $conn->prepare("DELETE FROM menu WHERE id_menu = ?");
    $stmt_delete->bind_param("i", $id);
    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data menu");
    }
    $stmt_delete->close();
    $imageDeleted = false;
    if ($imageUrl) {
        $fileName = basename($imageUrl);
        $filePath = $UPLOAD_DIR . $fileName;
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $imageDeleted = true;
            }
        }
    }
    echo json_encode([
        'success' => true,
        'message' => 'Menu berhasil dihapus!',
        'image_deleted' => $imageDeleted
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
$conn->close();

