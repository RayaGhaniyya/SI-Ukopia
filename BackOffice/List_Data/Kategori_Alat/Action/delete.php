<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode([
        'success' => false, 
        'message' => 'Method tidak valid'
    ]));
}
try {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('ID kategori tidak valid!');
    }
    $stmt_check = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE id_kategori_alat = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        $stmt_check->close();
        throw new Exception('Data kategori tidak ditemukan!');
    }
    $stmt_check->close();
    $stmt_delete = $conn->prepare("DELETE FROM kategori_alat WHERE id_kategori_alat = ?");
    if (!$stmt_delete) {
        throw new Exception('Gagal menyiapkan query: ' . $conn->error);
    }
    $stmt_delete->bind_param("i", $id);
    if (!$stmt_delete->execute()) {
        throw new Exception('Gagal menghapus data: ' . $stmt_delete->error);
    }
    $stmt_delete->close();
    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil dihapus!'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>

