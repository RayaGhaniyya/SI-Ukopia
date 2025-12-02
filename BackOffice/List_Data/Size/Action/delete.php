<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Size tidak valid!']);
    exit;
}
try {
    $stmt_check = $conn->prepare("SELECT id_size FROM size WHERE id_size = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        throw new Exception("Data Size tidak ditemukan!");
    }
    $stmt_check->close();
    $stmt_delete = $conn->prepare("DELETE FROM size WHERE id_size = ?");
    $stmt_delete->bind_param("i", $id);
    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data Size");
    }
    $stmt_delete->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'message' => 'Size berhasil dihapus!'
    ]);
} catch (Exception $e) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
