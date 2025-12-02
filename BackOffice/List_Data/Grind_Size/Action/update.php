<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_grind = intval($_POST['id_grind'] ?? 0);
$nama_grind = trim($_POST['nama_grind'] ?? '');

if ($id_grind <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}

if (empty($nama_grind)) {
    exit(json_encode(['success' => false, 'message' => 'Grind Size wajib diisi!']));
}

if (strlen($nama_grind) > 100) {
    exit(json_encode(['success' => false, 'message' => 'Grind Size maksimal 100 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_grind FROM grind_size WHERE id_grind = ?");
$stmt_check->bind_param("i", $id_grind);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    $stmt_check->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

$stmt_dup = $conn->prepare("SELECT id_grind FROM grind_size WHERE nama_grind = ? AND id_grind != ?");
$stmt_dup->bind_param("si", $nama_grind, $id_grind);
$stmt_dup->execute();
if ($stmt_dup->get_result()->num_rows > 0) {
    $stmt_dup->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Grind Size sudah digunakan!']));
}
$stmt_dup->close();

try {
    $stmt = $conn->prepare("UPDATE grind_size SET nama_grind = ? WHERE id_grind = ?");
    $stmt->bind_param("si", $nama_grind, $id_grind);

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data");
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Grind Size berhasil diperbarui!']);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

