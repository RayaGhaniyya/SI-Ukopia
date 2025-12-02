<?php
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$judul = trim($_POST['judul'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');

if (empty($judul) || empty($deskripsi) || empty($tanggal)) {
    exit(json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']));
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    exit(json_encode(['success' => false, 'message' => 'Format tanggal tidak valid!']));
}

if (!isset($_FILES['gambar']) || empty($_FILES['gambar']['tmp_name'][0])) {
    exit(json_encode(['success' => false, 'message' => 'Minimal 1 gambar harus diupload!']));
}

$totalFiles = count($_FILES['gambar']['tmp_name']);
if ($totalFiles > 4) {
    exit(json_encode(['success' => false, 'message' => 'Maksimal 4 gambar!']));
}

$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024;
$uploadedFiles = [];

for ($i = 0; $i < $totalFiles; $i++) {
    if (empty($_FILES['gambar']['tmp_name'][$i])) continue;

    $fileError = $_FILES['gambar']['error'][$i];
    $fileType = $_FILES['gambar']['type'][$i];
    $fileSize = $_FILES['gambar']['size'][$i];
    $fileName = $_FILES['gambar']['name'][$i];

    if ($fileError !== UPLOAD_ERR_OK) {
        exit(json_encode(['success' => false, 'message' => "Error upload: {$fileName}"]));
    }

    if (!in_array($fileType, $allowedTypes)) {
        exit(json_encode(['success' => false, 'message' => "File {$fileName} bukan gambar valid!"]));
    }

    if ($fileSize > $maxSize) {
        exit(json_encode(['success' => false, 'message' => "File {$fileName} terlalu besar! Max 5MB"]));
    }

    $uploadedFiles[] = [
        'tmp' => $_FILES['gambar']['tmp_name'][$i],
        'name' => $fileName
    ];
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO galery (judul, deskripsi, tanggal) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $judul, $deskripsi, $tanggal);

    if (!$stmt->execute()) throw new Exception("Gagal menyimpan data galeri");

    $id_galery = $conn->insert_id;
    $stmt->close();

    $uploadDir = "../../assets/img/gallery/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

    $stmt_detail = $conn->prepare("INSERT INTO detail_galery (id_galery, gambar) VALUES (?, ?)");

    foreach ($uploadedFiles as $index => $file) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = "gallery_{$id_galery}_" . time() . "_" . ($index + 1) . ".{$extension}";
        $targetPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp'], $targetPath)) {
            throw new Exception("Gagal upload: " . $file['name']);
        }

        $relativePath = "assets/img/gallery/" . $newFileName;
        $stmt_detail->bind_param("is", $id_galery, $relativePath);

        if (!$stmt_detail->execute()) {
            unlink($targetPath);
            throw new Exception("Gagal menyimpan detail gambar");
        }
    }

    $stmt_detail->close();
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Galeri berhasil ditambahkan!', 'id_galery' => $id_galery]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

