<?php
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php"); // FIXED: path dari action/ ke Mobile/
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$nama_menu = trim($_POST['nama_menu'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);

// Validasi
if (empty($nama_menu)) {
    exit(json_encode(['success' => false, 'message' => 'Nama menu wajib diisi!']));
}

if (empty($deskripsi)) {
    exit(json_encode(['success' => false, 'message' => 'Deskripsi wajib diisi!']));
}

if ($id_kategori <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori wajib dipilih!']));
}

// Validasi file upload
if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = 'Gambar wajib diupload!';
    if (isset($_FILES['gambar']['error'])) {
        switch ($_FILES['gambar']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'Ukuran file terlalu besar! Maksimal 5MB.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'Tidak ada file yang dipilih!';
                break;
        }
    }
    exit(json_encode(['success' => false, 'message' => $errorMsg]));
}

// Validasi tipe file
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$fileType = $_FILES['gambar']['type'];
if (!in_array($fileType, $allowedTypes)) {
    exit(json_encode(['success' => false, 'message' => 'Tipe file tidak valid! Gunakan JPG, PNG, atau WEBP.']));
}

// Validasi ukuran file (5MB)
if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
    exit(json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar! Maksimal 5MB.']));
}

// Config upload
$BASE_URL = "http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/";
$UPLOAD_DIR = '../../Uploads/Menu/'; // Relative dari action/

if (!is_dir($UPLOAD_DIR)) {
    if (!mkdir($UPLOAD_DIR, 0755, true)) {
        exit(json_encode(['success' => false, 'message' => 'Gagal membuat direktori upload!']));
    }
}

// Upload & optimize image
$newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);

if (!$newFileName) {
    exit(json_encode(['success' => false, 'message' => 'Gagal mengoptimalkan gambar!']));
}

$gambar_url = $BASE_URL . $newFileName;

// Begin transaction
$conn->begin_transaction();

try {
    // Insert to database
    $stmt = $conn->prepare("INSERT INTO menu (nama_menu, deskripsi, id_kategori, gambar_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $nama_menu, $deskripsi, $id_kategori, $gambar_url);

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data: ' . $stmt->error);
    }

    $id_menu = $conn->insert_id;
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Menu berhasil ditambahkan!', 
        'id_menu' => $id_menu
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Delete uploaded file
    if (file_exists($UPLOAD_DIR . $newFileName)) {
        @unlink($UPLOAD_DIR . $newFileName);
    }
        echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

$conn->close();
