<?php
// [UBAH] Path koneksi sesuai lokasi
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// [UBAH] Ambil data
$id_alat = intval($_POST['id_alat'] ?? 0);
$nama_alat = trim($_POST['nama_alat'] ?? '');
$id_kategori = intval($_POST['id_kategori'] ?? 0);

// Validasi ID
if ($id_alat <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Alat tidak valid!']);
    exit;
}

// Validasi Input
if (empty($nama_alat)) {
    echo json_encode(['success' => false, 'message' => 'Nama Alat wajib diisi!']);
    exit;
}

if ($id_kategori <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kategori Alat wajib dipilih!']);
    exit;
}

$UPLOAD_DIR = '../../Uploads/Alat/';
$newFileName = null;

try {
    // 1. [UBAH] Cek data lama (untuk ambil nama gambar lama)
    $stmt_check = $conn->prepare("SELECT id_alat, gambar FROM alat WHERE id_alat = ?");
    $stmt_check->bind_param("i", $id_alat);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Data Alat tidak ditemukan!");
    }
    
    $oldData = $result_check->fetch_assoc();
    $gambarFinal = $oldData['gambar']; // Default: pakai gambar lama
    $stmt_check->close();

    // 2. Cek apakah ada upload gambar baru
    $hasNewImage = false;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        // Buat folder jika belum ada
        if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

        $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
        
        if (!$newFileName) {
            throw new Exception("Gagal memproses gambar baru");
        }

        $gambarFinal = $newFileName; // Update variable gambar
        $hasNewImage = true;
    }

    // 3. [UBAH] Query Update
    $stmt = $conn->prepare("UPDATE alat SET nama_alat = ?, id_kategori_alat = ?, gambar = ? WHERE id_alat = ?");
    $stmt->bind_param("sisi", $nama_alat, $id_kategori, $gambarFinal, $id_alat);

    if (!$stmt->execute()) {
        throw new Exception("Gagal memperbarui data Alat");
    }
    
    $stmt->close();
    $conn->close();

    // 4. Bersihkan gambar lama jika sukses update dan ada gambar baru
    if ($hasNewImage && !empty($oldData['gambar'])) {
        $oldFilePath = $UPLOAD_DIR . $oldData['gambar'];
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    // [UBAH] Success message
    echo json_encode([
        'success' => true,
        'message' => 'Alat berhasil diperbarui!'
    ]);

} catch (Exception $e) {
    // Rollback: Hapus gambar BARU jika database gagal update
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