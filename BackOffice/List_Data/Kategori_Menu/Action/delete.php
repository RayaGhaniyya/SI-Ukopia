<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID kategori tidak valid!']);
    exit;
}

try {
    $stmt_check = $conn->prepare("SELECT id_kategori_menu FROM kategori_menu WHERE id_kategori_menu = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data kategori tidak ditemukan!");
    }
    $stmt_check->close();

    /*
    $stmt_rel = $conn->prepare("SELECT COUNT(*) as total FROM product WHERE id_kategori_menu = ?");
    $stmt_rel->bind_param("i", $id);
    $stmt_rel->execute();
    $rel_result = $stmt_rel->get_result()->fetch_assoc();
    if ($rel_result['total'] > 0) {
        throw new Exception("Kategori tidak dapat dihapus karena masih digunakan di produk!");
    }
    $stmt_rel->close();
    */

    $stmt_delete = $conn->prepare("DELETE FROM kategori_menu WHERE id_kategori_menu = ?");
    $stmt_delete->bind_param("i", $id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data kategori");
    }
    $stmt_delete->close();

    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil dihapus!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
