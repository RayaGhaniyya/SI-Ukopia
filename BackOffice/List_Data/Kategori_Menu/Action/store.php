<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

// [UBAH] Nama variable sesuai field
$nama_kategori = trim($_POST['nama_kategori'] ?? '');

// [UBAH] Validasi input
if (empty($nama_kategori)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}

// [UBAH] Validasi panjang karakter sesuai DB
if (strlen($nama_kategori) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter!']));
}

// [UBAH - OPTIONAL] Cek duplikasi jika perlu
$stmt_check = $conn->prepare("SELECT id_kategori_menu FROM kategori_menu WHERE nama_kategori = ?");
$stmt_check->bind_param("s", $nama_kategori);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori sudah ada!']));
}
$stmt_check->close();

try {
    // [UBAH] Query insert - nama tabel dan kolom
    $stmt = $conn->prepare("INSERT INTO kategori_menu (nama_kategori) VALUES (?)");
    $stmt->bind_param("s", $nama_kategori);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data kategori");
    }

    $id_kategori_menu = $conn->insert_id;
    $stmt->close();

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan!',
        'id_kategori_menu' => $id_kategori_menu
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
