<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

$id = intval($_POST['id_metode'] ?? 0);
$nama = trim($_POST['nama_metode'] ?? '');

if ($id <= 0 || empty($nama)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
    exit;
}

$UPLOAD_DIR = '../../Uploads/Metode/';
$newFileName = null;

try {
    $stmt_check = $conn->prepare("SELECT gambar_metode FROM metode WHERE id_metode = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows == 0) throw new Exception("Data tidak ditemukan!");
    $oldData = $result_check->fetch_assoc();
    $gambarFinal = $oldData['gambar_metode'];
    $stmt_check->close();

    $hasNewImage = false;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['svg', 'png', 'jpg', 'jpeg', 'webp'];

        if (!in_array($ext, $allowed)) throw new Exception("Format file harus SVG, PNG, atau JPG");

        $newFileName = 'metode_' . uniqid() . '.' . $ext;
        
        if (!move_uploaded_file($file['tmp_name'], $UPLOAD_DIR . $newFileName)) {
            throw new Exception("Gagal upload file");
        }

        $gambarFinal = $newFileName;
        $hasNewImage = true;
    }

    $stmt = $conn->prepare("UPDATE metode SET nama_metode = ?, gambar_metode = ? WHERE id_metode = ?");
    $stmt->bind_param("ssi", $nama, $gambarFinal, $id);

    if (!$stmt->execute()) throw new Exception("Gagal update database");
    
    $stmt->close();
    $conn->close();

    if ($hasNewImage && !empty($oldData['gambar_metode'])) {
        $oldPath = $UPLOAD_DIR . $oldData['gambar_metode'];
        if (file_exists($oldPath)) @unlink($oldPath);
    }

    echo json_encode(['success' => true, 'message' => 'Metode berhasil diperbarui!']);

} catch (Exception $e) {
    if ($newFileName && file_exists($UPLOAD_DIR . $newFileName)) @unlink($UPLOAD_DIR . $newFileName);
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>