<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_status = intval($_POST['id_status'] ?? 0);
$nama_status = trim($_POST['nama_status'] ?? '');

if ($id_status <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}

if (empty($nama_status)) {
    exit(json_encode(['success' => false, 'message' => 'Nama Status wajib diisi!']));
}

if (strlen($nama_status) > 50) {
    exit(json_encode(['success' => false, 'message' => 'Nama Status maksimal 50 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_status FROM status WHERE id_status = ?");
$stmt_check->bind_param("i", $id_status);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    $stmt_check->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

$stmt_dup = $conn->prepare("SELECT id_status FROM status WHERE nama_status = ? AND id_status != ?");
$stmt_dup->bind_param("si", $nama_status, $id_status);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    $stmt_dup->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Nama Status sudah digunakan!']));
}
$stmt_dup->close();

try {
    $stmt = $conn->prepare("UPDATE status SET nama_status = ? WHERE id_status = ?");
    $stmt->bind_param("si", $nama_status, $id_status);

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data");
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui!']);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
