<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$ukuran = trim($_POST['ukuran'] ?? '');

if (empty($ukuran)) {
    exit(json_encode(['success' => false, 'message' => 'Ukuran wajib diisi!']));
}

if (strlen($ukuran) > 50) {
    exit(json_encode(['success' => false, 'message' => 'Ukuran maksimal 50 karakter!']));
}

$stmt_check = $conn->prepare("SELECT id_size FROM size WHERE ukuran = ?");
$stmt_check->bind_param("s", $ukuran);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $stmt_check->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Ukuran sudah ada!']));
}
$stmt_check->close();

try {
    $stmt = $conn->prepare("INSERT INTO size (ukuran) VALUES (?)");
    $stmt->bind_param("s", $ukuran);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data Size");
    }

    $id_size = $conn->insert_id;
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Size berhasil ditambahkan!',
        'id_size' => $id_size
    ]);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>