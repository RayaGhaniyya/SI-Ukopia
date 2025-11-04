<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

// [UBAH] Nama variable sesuai field
$nama_kategori_alat = trim($_POST['nama_kategori_alat'] ?? '');

// [UBAH] Validasi input
if (empty($nama_kategori_alat)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}

// [UBAH] Validasi panjang karakter sesuai DB
if (strlen($nama_kategori_alat) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter!']));
}

// [UBAH - OPTIONAL] Cek duplikasi jika perlu
$stmt_check = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE nama_kategori_alat = ?");
$stmt_check->bind_param("s", $nama_kategori_alat);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori sudah ada!']));
}
$stmt_check->close();

try {
    // [UBAH] Query insert - nama tabel dan kolom
    $stmt = $conn->prepare("INSERT INTO kategori_alat (nama_kategori_alat) VALUES (?)");
    $stmt->bind_param("s", $nama_kategori_alat);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data kategori");
    }

    $id_kategori_alat = $conn->insert_id;
    $stmt->close();

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan!',
        'id_kategori_alat' => $id_kategori_alat
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
