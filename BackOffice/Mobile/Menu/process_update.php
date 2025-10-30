<?php
session_start();
include("../../../Koneksi/koneksi.php");
// PERBAIKAN: Path include helper_img.php (naik 1 level)
include("../helper_img.php");

// --- KONFIGURASI PENTING (DIPERBARUI) ---
$BASE_URL = "http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/"; 
$UPLOAD_DIR = '../Uploads/Menu/'; 
// --- SELESAI KONFIGURASI ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_menu = $_POST['id_menu'];
    $nama_menu = $_POST['nama_menu'];
    $deskripsi = $_POST['deskripsi'];
    $id_kategori = $_POST['id_kategori'];
    $gambar_url_lama = $_POST['gambar_url_lama'];
    $gambar_url_baru = $gambar_url_lama; // Defaultnya pakai gambar lama

    // Cek jika ada gambar BARU yang di-upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        
        $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
        
        if ($newFileName) {
            $gambar_url_baru = $BASE_URL . $newFileName;
            
            // Hapus gambar lama
            $oldFileName = str_replace($BASE_URL, '', $gambar_url_lama);
            $oldFilePath = $UPLOAD_DIR . $oldFileName;
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath); 
            }
        } else {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message'] = 'Gagal mengupload gambar baru.';
            header("Location: update.php?id=" . $id_menu);
            exit;
        }
    }

    // Query UPDATE
    $sql = "UPDATE menu SET 
                nama_menu = ?, 
                deskripsi = ?, 
                id_kategori = ?, 
                gambar_url = ? 
            WHERE id_menu = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssisi", $nama_menu, $deskripsi, $id_kategori, $gambar_url_baru, $id_menu);
        
        if ($stmt->execute()) {
            $_SESSION['message_type'] = 'success';
            $_SESSION['message'] = 'Data menu berhasil diperbarui!';
        } else {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message'] = 'Gagal memperbarui data: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message'] = 'Gagal mempersiapkan query: ' . $conn->error;
    }
    
    $conn->close();

} else {
    $_SESSION['message_type'] = 'error';
    $_SESSION['message'] = 'Akses tidak sah.';
}

header("Location: index.php");
exit;
?>