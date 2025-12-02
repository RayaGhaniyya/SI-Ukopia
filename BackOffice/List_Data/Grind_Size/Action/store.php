<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

// [UBAH] Nama variable sesuai field
$nama_grind = trim($_POST['nama_grind'] ?? '');

// [UBAH] Validasi input
if (empty($nama_grind)) {
    exit(json_encode(['success' => false, 'message' => 'Grind Size wajib diisi!']));
}

// [UBAH] Validasi panjang karakter sesuai DB
if (strlen($nama_grind) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Grind Size maksimal 100 karakter!']));
}

// [UBAH - OPTIONAL] Cek duplikasi jika perlu
$stmt_check = $conn->prepare("SELECT id_grind FROM grind_size WHERE nama_grind = ?");
$stmt_check->bind_param("s", $nama_grind);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $stmt_check->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Grind Size sudah ada!']));
}
$stmt_check->close();

try {
    // [UBAH] Query insert - nama tabel dan kolom
    $stmt = $conn->prepare("INSERT INTO grind_size (nama_grind) VALUES (?)");
    $stmt->bind_param("s", $nama_grind);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data Grind Size");
    }

    $id_grind = $conn->insert_id;
    $stmt->close();
    $conn->close();

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Grind Size berhasil ditambahkan!',
        'id_grind' => $id_grind
    ]);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
