<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$nama_status = trim($_POST['nama_status'] ?? '');

if (empty($nama_status)) {
    exit(json_encode(['success' => false, 'message' => 'Nama Status wajib diisi!']));
}

if (strlen($nama_status) > 50) {
    exit(json_encode(['success' => false, 'message' => 'Nama Status maksimal 50 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_status FROM status WHERE nama_status = ?");
$stmt_check->bind_param("s", $nama_status);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $stmt_check->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Nama Status sudah ada!']));
}
$stmt_check->close();

try {
    $stmt = $conn->prepare("INSERT INTO status (nama_status) VALUES (?)");
    $stmt->bind_param("s", $nama_status);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data Status");
    }

    $id_status = $conn->insert_id;
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Status berhasil ditambahkan!',
        'id_status' => $id_status
    ]);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>