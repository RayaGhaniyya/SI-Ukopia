<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// [UBAH] Nama parameter sesuai primary key
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Grind Size tidak valid!']);
    exit;
}

try {
    // [UBAH] Cek apakah data exists
    $stmt_check = $conn->prepare("SELECT id_grind FROM grind_size WHERE id_grind = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data Grind Size tidak ditemukan!");
    }
    $stmt_check->close();

    // [UBAH - OPTIONAL] Cek relasi dengan tabel lain (jika ada foreign key)
    // Contoh: cek apakah grind size sudah digunakan di tabel product
    /*
    $stmt_rel = $conn->prepare("SELECT COUNT(*) as total FROM product WHERE id_grind = ?");
    $stmt_rel->bind_param("i", $id);
    $stmt_rel->execute();
    $rel_result = $stmt_rel->get_result()->fetch_assoc();
    if ($rel_result['total'] > 0) {
        throw new Exception("Grind Size tidak dapat dihapus karena masih digunakan di produk!");
    }
    $stmt_rel->close();
    */

    // [UBAH] Hapus data - nama tabel dan kolom
    $stmt_delete = $conn->prepare("DELETE FROM grind_size WHERE id_grind = ?");
    $stmt_delete->bind_param("i", $id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data Grind Size");
    }
    $stmt_delete->close();
    $conn->close();

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Grind Size berhasil dihapus!'
    ]);
} catch (Exception $e) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
