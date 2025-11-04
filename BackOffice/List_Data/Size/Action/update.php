<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_size = intval($_POST['id_size'] ?? 0);
$ukuran = trim($_POST['ukuran'] ?? '');

if ($id_size <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}

if (empty($ukuran)) {
    exit(json_encode(['success' => false, 'message' => 'Ukuran wajib diisi!']));
}

if (strlen($ukuran) > 50) {
    exit(json_encode(['success' => false, 'message' => 'Ukuran maksimal 50 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_size FROM size WHERE id_size = ?");
$stmt_check->bind_param("i", $id_size);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    $stmt_check->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

$stmt_dup = $conn->prepare("SELECT id_size FROM size WHERE ukuran = ? AND id_size != ?");
$stmt_dup->bind_param("si", $ukuran, $id_size);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    $stmt_dup->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Ukuran sudah digunakan!']));
}
$stmt_dup->close();

try {
    $stmt = $conn->prepare("UPDATE size SET ukuran = ? WHERE id_size = ?");
    $stmt->bind_param("si", $ukuran, $id_size);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal update data");
    }
    
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Size berhasil diperbarui!']);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>