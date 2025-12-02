<?php
// 1. Matikan laporan error ke layar (agar tidak merusak JSON)
error_reporting(0);
ini_set('display_errors', 0);

// 2. Mulai Output Buffering (Tahan semua output)
ob_start();

include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php"); 

// 3. Bersihkan buffer (Hapus output apa pun yang mungkin muncul dari include di atas)
ob_clean();

// 4. Set Header JSON
header('Content-Type: application/json');

$UPLOAD_DIR = '../../Uploads/Promo/';
if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

$response = [];

if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
    // Gunakan helper image
    $filename = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);

    if ($filename) {
        $stmt = $conn->prepare("INSERT INTO promo (gambar) VALUES (?)");
        $stmt->bind_param("s", $filename);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Promo berhasil diupload!'];
        } else {
            // Hapus file jika gagal simpan DB
            @unlink($UPLOAD_DIR . $filename);
            $response = ['success' => false, 'message' => 'Gagal simpan database'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Gagal memproses gambar'];
    }
} else {
    $response = ['success' => false, 'message' => 'Pilih gambar yang valid!'];
}

// 5. Pastikan tidak ada output lain sebelum JSON
ob_clean(); 
echo json_encode($response);
exit;
?>