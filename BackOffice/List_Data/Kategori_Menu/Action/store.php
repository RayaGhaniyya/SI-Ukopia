<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}
$nama_kategori = trim($_POST['nama_kategori'] ?? '');
$biji = intval($_POST['biji'] ?? 0); // [BARU] Ambil status biji
if (empty($nama_kategori)) {
    exit(json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi!']));
}
$stmt_check = $conn->prepare("SELECT id_kategori_menu FROM kategori_menu WHERE nama_kategori = ?");
$stmt_check->bind_param("s", $nama_kategori);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori sudah ada!']));
}
$stmt_check->close();
try {
    $stmt = $conn->prepare("INSERT INTO kategori_menu (nama_kategori, biji) VALUES (?, ?)");
    $stmt->bind_param("si", $nama_kategori, $biji);
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data kategori");
    }
    $id = $conn->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Kategori berhasil ditambahkan!', 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>

