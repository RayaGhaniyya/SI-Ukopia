<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_kategori = intval($_POST['id_kategori'] ?? 0);
$nama_kategori = trim($_POST['nama_kategori'] ?? '');

if ($id_kategori <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}

if (empty($nama_kategori)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}

if (strlen($nama_kategori) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_kategori FROM kategori WHERE id_kategori = ?");
$stmt_check->bind_param("i", $id_kategori);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

$stmt_dup = $conn->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ? AND id_kategori != ?");
$stmt_dup->bind_param("si", $nama_kategori, $id_kategori);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori sudah digunakan!']));
}
$stmt_dup->close();

try {
    $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
    $stmt->bind_param("si", $nama_kategori, $id_kategori);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal update data");
    }
    
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Kategori berhasil diperbarui!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
