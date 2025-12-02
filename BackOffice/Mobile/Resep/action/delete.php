<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$id_resep = intval($_POST['id_resep'] ?? 0);

if ($id_resep <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID Resep tidak valid!']));
}

try {
    
    $stmt = $conn->prepare("DELETE FROM resep WHERE id_resep = ?");
    $stmt->bind_param("i", $id_resep);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Resep berhasil dihapus!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
        }
    } else {
        throw new Exception("Gagal eksekusi query delete.");
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
