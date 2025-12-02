<?php
// action/store.php - Tambah Alat dengan Shared Image Support
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// Ambil data dari POST
$nama_alat = trim($_POST['nama_alat'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);
$image_option = $_POST['image_option'] ?? 'new'; // 'existing' atau 'new'

// Validasi input
if (empty($nama_alat)) {
    echo json_encode(['success' => false, 'message' => 'Nama Alat wajib diisi!']);
    exit;
}

if ($id_kategori <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kategori Alat wajib dipilih!']);
    exit;
}

// Konfigurasi Upload
$UPLOAD_DIR = '../../Uploads/Alat/';
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

$gambarFinal = null;
$needUpload = false;

try {
    // OPSI 1: Gunakan gambar yang sudah ada
    if ($image_option === 'existing') {
        $existing_image = trim($_POST['existing_image'] ?? '');
        
        if (empty($existing_image)) {
            throw new Exception("Pilih gambar yang sudah ada atau upload gambar baru!");
        }
        
        // Validasi file exists
        if (!file_exists($UPLOAD_DIR . $existing_image)) {
            throw new Exception("Gambar yang dipilih tidak ditemukan!");
        }
        
        $gambarFinal = $existing_image;
    } 
    // OPSI 2: Upload gambar baru
    else {
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Gambar Alat wajib diupload!');
        }
        
        $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
        
        if (!$newFileName) {
            throw new Exception("Gagal memproses/mengompres gambar!");
        }
        
        $gambarFinal = $newFileName;
        $needUpload = true;
    }

    // Cek duplikasi nama alat dalam kategori yang sama
    $stmt_dup = $conn->prepare("SELECT id_alat FROM alat WHERE nama_alat = ? AND id_kategori_alat = ?");
    $stmt_dup->bind_param("si", $nama_alat, $id_kategori);
    $stmt_dup->execute();
    
    if ($stmt_dup->get_result()->num_rows > 0) {
        $stmt_dup->close();
        
        // Rollback: hapus gambar baru jika sudah upload
        if ($needUpload && file_exists($UPLOAD_DIR . $gambarFinal)) {
            @unlink($UPLOAD_DIR . $gambarFinal);
        }
        
        throw new Exception("Alat dengan nama '{$nama_alat}' sudah ada di kategori ini!");
    }
    $stmt_dup->close();

    // Insert data ke database
    $stmt = $conn->prepare("INSERT INTO alat (nama_alat, id_kategori_alat, gambar) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $nama_alat, $id_kategori, $gambarFinal);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data Alat ke database");
    }

    $id_alat = $conn->insert_id;
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Alat berhasil ditambahkan!',
        'id_alat' => $id_alat,
        'image_reused' => ($image_option === 'existing')
    ]);

} catch (Exception $e) {
    // Rollback: Hapus gambar baru jika database gagal
    if ($needUpload && $gambarFinal && file_exists($UPLOAD_DIR . $gambarFinal)) {
        @unlink($UPLOAD_DIR . $gambarFinal);
    }
    
    if (isset($conn)) $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>