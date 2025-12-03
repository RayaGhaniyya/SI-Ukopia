<?php
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php"); 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

$nama = trim($_POST['nama_metode'] ?? '');

if (empty($nama)) {
    echo json_encode(['success' => false, 'message' => 'Nama Metode wajib diisi!']);
    exit;
}


if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Icon Metode wajib diupload!']);
    exit;
}

$UPLOAD_DIR = '../../Uploads/Metode/';
if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

$newFileName = null;

try {
    
    
    $file = $_FILES['gambar'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['svg', 'png', 'jpg', 'jpeg', 'webp'];

    if (!in_array($ext, $allowed)) throw new Exception("Format file harus SVG, PNG, atau JPG");

    $newFileName = 'metode_' . uniqid() . '.' . $ext;
    
    if (!move_uploaded_file($file['tmp_name'], $UPLOAD_DIR . $newFileName)) {
        throw new Exception("Gagal upload file");
    }

    $stmt = $conn->prepare("INSERT INTO metode (nama_metode, gambar_metode) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $newFileName);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan database");
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Metode berhasil ditambahkan!']);

} catch (Exception $e) {
    if ($newFileName && file_exists($UPLOAD_DIR . $newFileName)) @unlink($UPLOAD_DIR . $newFileName);
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>