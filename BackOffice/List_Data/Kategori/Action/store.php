<?php

include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}


$nama_kategori = trim($_POST['nama_kategori'] ?? '');


if (empty($nama_kategori)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}


if (strlen($nama_kategori) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter!']));
}


$stmt_check = $conn->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
$stmt_check->bind_param("s", $nama_kategori);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori sudah ada!']));
}
$stmt_check->close();

try {
    
    $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
    $stmt->bind_param("s", $nama_kategori);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data kategori");
    }

    $id_kategori = $conn->insert_id;
    $stmt->close();

    
    echo json_encode([
        'success' => true, 
        'message' => 'Kategori berhasil ditambahkan!', 
        'id_kategori' => $id_kategori
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();