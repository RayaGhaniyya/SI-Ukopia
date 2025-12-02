<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

// [UBAH] Nama variable sesuai field
$id_kategori_alat = intval($_POST['id_kategori_alat'] ?? 0);
$nama_kategori_alat = trim($_POST['nama_kategori_alat'] ?? '');

// Validasi
if ($id_kategori_alat <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}

// [UBAH] Validasi input
if (empty($nama_kategori_alat)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}

// [UBAH] Validasi panjang
if (strlen($nama_kategori_alat) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter!']));
}

// [UBAH] Cek exists
$stmt_check = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE id_kategori_alat = ?");
$stmt_check->bind_param("i", $id_kategori_alat);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

// [UBAH - OPTIONAL] Cek duplikasi nama (kecuali data sendiri)
$stmt_dup = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE nama_kategori_alat = ? AND id_kategori_alat != ?");
$stmt_dup->bind_param("si", $nama_kategori_alat, $id_kategori_alat);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori sudah digunakan!']));
}
$stmt_dup->close();

try {
    // [UBAH] Query update - nama tabel dan kolom
    $stmt = $conn->prepare("UPDATE kategori_alat SET nama_kategori_alat = ? WHERE id_kategori_alat = ?");
    $stmt->bind_param("si", $nama_kategori_alat, $id_kategori_alat);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal update data");
    }
    
    $stmt->close();

    // [UBAH] Success message
    echo json_encode(['success' => true, 'message' => 'Kategori berhasil diperbarui!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();