<?php
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php");
header('Content-Type: application/json');

error_log("===== UPDATE MENU START =====");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_menu = intval($_POST['id_menu'] ?? 0);
$nama_menu = trim($_POST['nama_menu'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);

if ($id_menu <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
}
if (empty($nama_menu)) {
    exit(json_encode(['success' => false, 'message' => 'Nama menu wajib diisi!']));
}
if (empty($deskripsi)) {
    exit(json_encode(['success' => false, 'message' => 'Deskripsi wajib diisi!']));
}
if ($id_kategori <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Kategori wajib dipilih!']));
}

$stmt_check = $conn->prepare("SELECT gambar_url FROM menu WHERE id_menu = ?");
$stmt_check->bind_param("i", $id_menu);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}

$oldData = $result->fetch_assoc();
$gambar_url = $oldData['gambar_url']; // Default: tetap pakai gambar lama (bisa URL atau filename)
$stmt_check->close();

$BASE_URL = "http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/"; // Hanya untuk hapus
$UPLOAD_DIR = '../../Uploads/Menu/';

$hasNewImage = false;
$newFileName = null; // Inisialisasi

if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    error_log("New image detected, processing...");

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $fileType = $_FILES['gambar']['type'];
    if (!in_array($fileType, $allowedTypes)) {
        exit(json_encode(['success' => false, 'message' => 'Tipe file tidak valid! Gunakan JPG, PNG, atau WEBP.']));
    }

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
        error_log("New image saved: $newFileName");

        $gambar_url = $newFileName; // <-- UBAH BARIS INI
        $hasNewImage = true;

        $oldFileName = basename($oldData['gambar_url']); 
        $oldFilePath = $UPLOAD_DIR . $oldFileName;

        error_log("Deleting old image: $oldFilePath");
        if (file_exists($oldFilePath)) {
            if (@unlink($oldFilePath)) {
                error_log("Old image deleted successfully");
            } else {
                error_log("Failed to delete old image");
            }
        } else {
            error_log("Old image not found: $oldFilePath");
        }
    } else {
        exit(json_encode(['success' => false, 'message' => 'Gagal mengoptimalkan gambar baru!']));
    }
} else {
    error_log("No new image uploaded, keeping old image data");
    
    $gambar_url = basename($oldData['gambar_url']);

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("Upload error code: " . $_FILES['gambar']['error']);
    }
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("UPDATE menu SET nama_menu=?, deskripsi=?, id_kategori=?, gambar_url=? WHERE id_menu=?");
    $stmt->bind_param("ssisi", $nama_menu, $deskripsi, $id_kategori, $gambar_url, $id_menu);

    if (!$stmt->execute()) {
        throw new Exception('Gagal update data: ' . $stmt->error);
    }
    
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    $conn->commit();

    error_log("Update successful, affected rows: $affectedRows");
    error_log("===== UPDATE MENU END =====");

    echo json_encode([
        'success' => true,
        'message' => $hasNewImage
            ? 'Menu dan gambar berhasil diperbarui!'
            : 'Menu berhasil diperbarui!',
        'new_image' => $hasNewImage,
        'gambar_url' => $gambar_url
    ]);
} catch (Exception $e) {
    $conn->rollback();

    error_log("Update failed: " . $e->getMessage());
    error_log("===== UPDATE MENU END (ERROR) =====");

    if ($hasNewImage && isset($newFileName)) {
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
