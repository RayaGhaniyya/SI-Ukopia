<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_kategori_alat = intval($_POST['id_kategori_alat'] ?? 0);
$nama_kategori_alat = trim($_POST['nama_kategori_alat'] ?? '');

if ($id_kategori_alat <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}

if (empty($nama_kategori_alat)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}

if (strlen($nama_kategori_alat) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE id_kategori_alat = ?");
$stmt_check->bind_param("i", $id_kategori_alat);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

$stmt_dup = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE nama_kategori_alat = ? AND id_kategori_alat != ?");
$stmt_dup->bind_param("si", $nama_kategori_alat, $id_kategori_alat);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori sudah digunakan!']));
}
$stmt_dup->close();

try {
    $stmt = $conn->prepare("UPDATE kategori_alat SET nama_kategori_alat = ? WHERE id_kategori_alat = ?");
    $stmt->bind_param("si", $nama_kategori_alat, $id_kategori_alat);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal update data");
    }
    
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Kategori berhasil diperbarui!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
