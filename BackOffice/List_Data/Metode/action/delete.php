<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid!']);
    exit;
}

$UPLOAD_DIR = '../../Uploads/Metode/';

try {
    $stmt_check = $conn->prepare("SELECT gambar_metode FROM metode WHERE id_metode = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$result) throw new Exception("Data tidak ditemukan!");


    $stmt = $conn->prepare("DELETE FROM metode WHERE id_metode = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if (!empty($result['gambar_metode'])) {
            $path = $UPLOAD_DIR . $result['gambar_metode'];
            if (file_exists($path)) @unlink($path);
        }
        echo json_encode(['success' => true, 'message' => 'Metode berhasil dihapus!']);
    } else {
        throw new Exception("Gagal menghapus data");
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
