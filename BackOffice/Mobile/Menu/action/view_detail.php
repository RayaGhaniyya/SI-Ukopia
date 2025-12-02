<?php
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php"); // FIXED: path dari action/ ke Mobile/
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_menu = intval($_POST['id_menu'] ?? 0);
$nama_menu = trim($_POST['nama_menu'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);

// Validasi
if ($id_menu <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}
// ... (Validasi lainnya) ...
if (empty($nama_menu)) {
    exit(json_encode(['success' => false, 'message' => 'Nama menu wajib diisi!']));
}
if (empty($deskripsi)) {
    exit(json_encode(['success' => false, 'message' => 'Deskripsi wajib diisi!']));
}
if ($id_kategori <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori wajib dipilih!']));
}

// Cek menu exists
$stmt_check = $conn->prepare("SELECT gambar_url FROM menu WHERE id_menu = ?");
$stmt_check->bind_param("i", $id_menu);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}

$oldData = $result->fetch_assoc();
$gambar_url = $oldData['gambar_url']; // Default: keep old image
$stmt_check->close();

// Config upload
$BASE_URL = "http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/"; // Hanya untuk hapus
$UPLOAD_DIR = '../../Uploads/Menu/'; // Relative dari action/

$newFileName = null; // Inisialisasi
$hasNewImage = false;

// Cek jika ada gambar baru
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    // ... (Validasi tipe file) ...
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $fileType = $_FILES['gambar']['type'];
    if (!in_array($fileType, $allowedTypes)) {
        exit(json_encode(['success' => false, 'message' => 'Tipe file tidak valid! Gunakan JPG, PNG, atau WEBP.']));
    }

    // ... (Validasi ukuran file) ...
    if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
        exit(json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar! Maksimal 5MB.']));
    }

    if (!is_dir($UPLOAD_DIR)) {
        if (!mkdir($UPLOAD_DIR, 0755, true)) {
            exit(json_encode(['success' => false, 'message' => 'Gagal membuat direktori upload!']));
        }
    }

    $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);

    if ($newFileName) {
        // ==========================================================
        // PERUBAHAN 1: Simpan hanya nama file
        // ==========================================================
        $gambar_url = $newFileName; // <-- UBAH BARIS INI
        $hasNewImage = true;
        // ==========================================================


        // Delete old image
        // ==========================================================
        // PERUBAHAN 2: Gunakan basename untuk keamanan
        // ==========================================================
        $oldFileName = basename($oldData['gambar_url']); // <-- UBAH BARIS INI
        $oldFilePath = $UPLOAD_DIR . $oldFileName;
        // ==========================================================
        
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
    } else {
        exit(json_encode(['success' => false, 'message' => 'Gagal mengoptimalkan gambar baru!']));
    }
} else {
    // ==========================================================
    // PERUBAHAN 3: Pastikan data lama adalah filename
    // ==========================================================
    $gambar_url = basename($oldData['gambar_url']); // <-- UBAH BARIS INI
    // ==========================================================
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update database
    $stmt = $conn->prepare("UPDATE menu SET nama_menu=?, deskripsi=?, id_kategori=?, gambar_url=? WHERE id_menu=?");
    $stmt->bind_param("ssisi", $nama_menu, $deskripsi, $id_kategori, $gambar_url, $id_menu);

    if (!$stmt->execute()) {
        throw new Exception('Gagal update data: ' . $stmt->error);
    }

    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Menu berhasil diperbarui!'
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Jika update DB gagal, hapus gambar baru yang terlanjur diupload
    if ($hasNewImage && $newFileName) {
        $newFilePath = $UPLOAD_DIR . $newFileName;
        if (file_exists($newFilePath)) {
            @unlink($newFilePath);
        }
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>