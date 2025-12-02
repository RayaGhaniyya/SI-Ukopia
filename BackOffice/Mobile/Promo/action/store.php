<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
include("../../../../Koneksi/koneksi.php");
include("../../helper_img.php"); 
ob_clean();
header('Content-Type: application/json');
$UPLOAD_DIR = '../../Uploads/Promo/';
if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);
$response = [];
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
    $filename = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
    if ($filename) {
        $stmt = $conn->prepare("INSERT INTO promo (gambar) VALUES (?)");
        $stmt->bind_param("s", $filename);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Promo berhasil diupload!'];
        } else {
            @unlink($UPLOAD_DIR . $filename);
            $response = ['success' => false, 'message' => 'Gagal simpan database'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Gagal memproses gambar'];
    }
} else {
    $response = ['success' => false, 'message' => 'Pilih gambar yang valid!'];
}
ob_clean(); 
echo json_encode($response);
exit;
?>

