<?php
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID galeri tidak valid!']);
    exit;
}
$conn->begin_transaction();
try {
    $stmt_check = $conn->prepare("SELECT id_galery FROM galery WHERE id_galery = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        throw new Exception("Data galeri tidak ditemukan!");
    }
    $stmt_check->close();
    $stmt_images = $conn->prepare("SELECT gambar FROM detail_galery WHERE id_galery = ?");
    $stmt_images->bind_param("i", $id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();
    $imagePaths = [];
    while ($row = $result_images->fetch_assoc()) {
        $imagePaths[] = $row['gambar'];
    }
    $stmt_images->close();
    $stmt_delete_detail = $conn->prepare("DELETE FROM detail_galery WHERE id_galery = ?");
    $stmt_delete_detail->bind_param("i", $id);
    if (!$stmt_delete_detail->execute()) {
        throw new Exception("Gagal menghapus detail gambar dari database");
    }
    $stmt_delete_detail->close();
    $stmt_delete = $conn->prepare("DELETE FROM galery WHERE id_galery = ?");
    $stmt_delete->bind_param("i", $id);
    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data galeri");
    }
    $stmt_delete->close();
    $conn->commit();
    foreach ($imagePaths as $path) {
        $fullPath = dirname(__DIR__, 3) . "/" . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    echo json_encode([
        'success' => true,
        'message' => 'Galeri berhasil dihapus!',
        'deleted_images' => count($imagePaths)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
$conn->close();

