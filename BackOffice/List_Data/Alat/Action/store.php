<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
// [UBAH] Sertakan helper untuk kompresi gambar
include("../../helper_img.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// [UBAH] Ambil data dari POST
$nama_alat = trim($_POST['nama_alat'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0); // Dari dropdown form

// [UBAH] Validasi input
if (empty($nama_alat)) {
    echo json_encode(['success' => false, 'message' => 'Nama Alat wajib diisi!']);
    exit;
}

if ($id_kategori <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kategori Alat wajib dipilih!']);
}

// [UBAH] Validasi Gambar Wajib Ada di Store
if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Gambar Alat wajib diupload!']);
    exit;
}

// Konfigurasi Upload
$UPLOAD_DIR = '../../Uploads/Alat/';
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

$newFileName = null;

try {
    // 1. Proses Upload Gambar (Menggunakan Helper)
    $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
    
    if (!$newFileName) {
        throw new Exception("Gagal memproses/mengompres gambar!");
    }

    // 2. [UBAH] Query Insert (Perhatikan nama kolom id_kategori_alat)
    $stmt = $conn->prepare("INSERT INTO alat (nama_alat, id_kategori_alat, gambar) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $nama_alat, $id_kategori, $newFileName);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data Alat ke database");
    }

    $stmt->close();
    $conn->close();

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Alat berhasil ditambahkan!'
    ]);

} catch (Exception $e) {
    // Rollback: Hapus gambar jika database gagal
    if ($newFileName && file_exists($UPLOAD_DIR . $newFileName)) {
        @unlink($UPLOAD_DIR . $newFileName);
    }
    
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>