<?php
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

$id_alat = intval($_POST['id_alat'] ?? 0);
$nama_alat = trim($_POST['nama_alat'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);
$image_option = $_POST['image_option'] ?? 'keep'; // 'keep', 'existing', 'new'
$old_image = $_POST['old_image'] ?? '';

if ($id_alat <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Alat tidak valid!']);
    exit;
}

if (empty($nama_alat)) {
    echo json_encode(['success' => false, 'message' => 'Nama Alat wajib diisi!']);
    exit;
}

if ($id_kategori <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kategori Alat wajib dipilih!']);
    exit;
}

$UPLOAD_DIR = '../../Uploads/Alat/';
$gambarFinal = $old_image; // Default: gunakan gambar lama
$needDeleteOld = false;
$newFileName = null;

try {
    $stmt_check = $conn->prepare("SELECT id_alat, gambar FROM alat WHERE id_alat = ?");
    $stmt_check->bind_param("i", $id_alat);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data Alat tidak ditemukan!");
    }
    
    $oldData = $result_check->fetch_assoc();
    $stmt_check->close();

    if ($image_option === 'keep') {
        $gambarFinal = $oldData['gambar'];
        
    } else if ($image_option === 'existing') {
        $existing_image = trim($_POST['existing_image'] ?? '');
        
        if (empty($existing_image)) {
            throw new Exception("Pilih gambar yang akan digunakan!");
        }
        
        if (!file_exists($UPLOAD_DIR . $existing_image)) {
            throw new Exception("Gambar yang dipilih tidak ditemukan!");
        }
        
        $gambarFinal = $existing_image;
        
        $needDeleteOld = shouldDeleteImage($conn, $oldData['gambar'], $id_alat);
        
    } else if ($image_option === 'new') {
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload gambar baru atau pilih opsi lain!');
        }
        
        if (!is_dir($UPLOAD_DIR)) {
            mkdir($UPLOAD_DIR, 0755, true);
        }

        $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
        
        if (!$newFileName) {
            throw new Exception("Gagal memproses gambar baru!");
        }

        $gambarFinal = $newFileName;
        
        $needDeleteOld = shouldDeleteImage($conn, $oldData['gambar'], $id_alat);
    }

    $stmt_dup = $conn->prepare("SELECT id_alat FROM alat WHERE nama_alat = ? AND id_kategori_alat = ? AND id_alat != ?");
    $stmt_dup->bind_param("sii", $nama_alat, $id_kategori, $id_alat);
    $stmt_dup->execute();
    
    if ($stmt_dup->get_result()->num_rows > 0) {
        $stmt_dup->close();
        
        if ($newFileName && file_exists($UPLOAD_DIR . $newFileName)) {
            @unlink($UPLOAD_DIR . $newFileName);
        }
        
        throw new Exception("Alat dengan nama '{$nama_alat}' sudah ada di kategori ini!");
    }
    $stmt_dup->close();

    $stmt = $conn->prepare("UPDATE alat SET nama_alat = ?, id_kategori_alat = ?, gambar = ? WHERE id_alat = ?");
    $stmt->bind_param("sisi", $nama_alat, $id_kategori, $gambarFinal, $id_alat);

    if (!$stmt->execute()) {
        throw new Exception("Gagal memperbarui data Alat");
    }
    
    $stmt->close();
    $conn->close();

    if ($needDeleteOld && !empty($oldData['gambar'])) {
        $oldFilePath = $UPLOAD_DIR . $oldData['gambar'];
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Alat berhasil diperbarui!',
        'old_image_deleted' => $needDeleteOld
    ]);

} catch (Exception $e) {
    if ($newFileName && file_exists($UPLOAD_DIR . $newFileName)) {
        @unlink($UPLOAD_DIR . $newFileName);
    }

    if (isset($conn)) $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Cek apakah gambar masih digunakan oleh alat lain
 * @return bool true jika bisa dihapus (tidak digunakan lagi)
 */
function shouldDeleteImage($conn, $imageName, $excludeId) {
    if (empty($imageName)) return false;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM alat WHERE gambar = ? AND id_alat != ?");
    $stmt->bind_param("si", $imageName, $excludeId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return ($result['count'] == 0);
}
?>
