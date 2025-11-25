<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid']));
}

try {
    // Karena ada ON DELETE CASCADE di database (Constraint), 
    // kita cukup hapus parent-nya (resep), detail-nya otomatis hilang.
    
    $stmt = $conn->prepare("DELETE FROM resep WHERE id_resep = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Resep berhasil dihapus!']);
    } else {
        throw new Exception("Gagal menghapus data.");
    }
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>