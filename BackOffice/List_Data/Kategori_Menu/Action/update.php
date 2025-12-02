<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit(json_encode(['success'=>false]));

$id = intval($_POST['id_kategori_menu'] ?? 0);
$nama = trim($_POST['nama_kategori'] ?? '');
$biji = intval($_POST['biji'] ?? 0); // [BARU]

if ($id <= 0 || empty($nama)) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak valid!']));
}

try {
    // [UBAH] Update kolom biji
    $stmt = $conn->prepare("UPDATE kategori_menu SET nama_kategori = ?, biji = ? WHERE id_kategori_menu = ?");
    $stmt->bind_param("sii", $nama, $biji, $id);
    
    if (!$stmt->execute()) throw new Exception("Gagal update data");
    
    echo json_encode(['success' => true, 'message' => 'Kategori diperbarui!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>