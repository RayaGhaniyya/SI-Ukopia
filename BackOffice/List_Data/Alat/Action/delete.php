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
    echo json_encode(['success' => false, 'message' => 'ID Alat tidak valid!']);
    exit;
}

// [UBAH] Konfigurasi folder upload untuk penghapusan gambar
$UPLOAD_DIR = '../../Uploads/Alat/';

try {
    // [UBAH] Cek apakah data exists DAN ambil nama gambar
    $stmt_check = $conn->prepare("SELECT id_alat, gambar FROM alat WHERE id_alat = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data Alat tidak ditemukan!");
    }
    
    $row = $result_check->fetch_assoc();
    $stmt_check->close();

    // [UBAH] Hapus data - nama tabel dan kolom
    $stmt_delete = $conn->prepare("DELETE FROM alat WHERE id_alat = ?");
    $stmt_delete->bind_param("i", $id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal menghapus data Alat");
    }
    $stmt_delete->close();
    $conn->close();

    // [TAMBAHAN] Hapus File Fisik Gambar
    if (!empty($row['gambar'])) {
        $filePath = $UPLOAD_DIR . $row['gambar'];
        // Cek apakah file ada, lalu hapus
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Alat berhasil dihapus!'
    ]);

} catch (Exception $e) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>