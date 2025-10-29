<?php
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// Validasi input
$judul = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';

if (empty($judul) || empty($deskripsi) || empty($tanggal)) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']);
    exit;
}

// Validasi tanggal format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid!']);
    exit;
}

// Validasi file upload
if (!isset($_FILES['gambar']) || empty($_FILES['gambar']['tmp_name'][0])) {
    echo json_encode(['success' => false, 'message' => 'Minimal 1 gambar harus diupload!']);
    exit;
}

// Validasi jumlah file (max 4)
$totalFiles = count($_FILES['gambar']['tmp_name']);
if ($totalFiles > 4) {
    echo json_encode(['success' => false, 'message' => 'Maksimal 4 gambar!']);
    exit;
}

// Validasi dan prepare file upload
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB
$uploadedFiles = [];

for ($i = 0; $i < $totalFiles; $i++) {
    if (empty($_FILES['gambar']['tmp_name'][$i])) continue;

    $fileName = $_FILES['gambar']['name'][$i];
    $fileTmp = $_FILES['gambar']['tmp_name'][$i];
    $fileSize = $_FILES['gambar']['size'][$i];
    $fileType = $_FILES['gambar']['type'][$i];
    $fileError = $_FILES['gambar']['error'][$i];

    // Validasi error
    if ($fileError !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => "Error upload file: {$fileName}"]);
        exit;
    }

    // Validasi tipe file
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => "File {$fileName} bukan gambar yang valid!"]);
        exit;
    }

    // Validasi ukuran file
    if ($fileSize > $maxSize) {
        echo json_encode(['success' => false, 'message' => "File {$fileName} terlalu besar! Max 5MB"]);
        exit;
    }

    $uploadedFiles[] = [
        'tmp' => $fileTmp,
        'name' => $fileName,
        'type' => $fileType
    ];
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Insert data galeri dengan prepared statement
    $stmt = $conn->prepare("INSERT INTO galery (judul, deskripsi, tanggal) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $judul, $deskripsi, $tanggal);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data galeri");
    }

    $id_galery = $conn->insert_id;
    $stmt->close();

    // Buat folder jika belum ada
    $uploadDir = "../../assets/img/gallery/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Upload dan simpan detail gambar
    $stmt_detail = $conn->prepare("INSERT INTO detail_galery (id_galery, gambar) VALUES (?, ?)");

    foreach ($uploadedFiles as $index => $file) {
        // Generate nama file unik
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = "gallery_" . $id_galery . "_" . time() . "_" . ($index + 1) . "." . $extension;
        $targetPath = $uploadDir . $newFileName;

        // Upload file
        if (!move_uploaded_file($file['tmp'], $targetPath)) {
            throw new Exception("Gagal upload file: " . $file['name']);
        }

        // Simpan path ke database (relative path)
        $relativePath = "assets/img/gallery/" . $newFileName;
        $stmt_detail->bind_param("is", $id_galery, $relativePath);

        if (!$stmt_detail->execute()) {
            // Hapus file yang sudah diupload jika gagal insert
            unlink($targetPath);
            throw new Exception("Gagal menyimpan detail gambar");
        }
    }

    $stmt_detail->close();

    // Commit transaksi
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Galeri berhasil ditambahkan!',
        'id_galery' => $id_galery
    ]);
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();

    // Hapus file yang sudah diupload
    foreach ($uploadedFiles as $file) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = "gallery_" . $id_galery . "_" . time() . "_*.{$extension}";
        $files = glob($uploadDir . $newFileName);
        foreach ($files as $f) {
            if (file_exists($f)) unlink($f);
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
