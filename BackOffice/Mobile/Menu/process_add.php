<?php
session_start();
// Path koneksi sudah benar
include("../../../Koneksi/koneksi.php");
// PERBAIKAN: Path include helper_img.php (naik 1 level)
include("../helper_img.php"); 

// --- KONFIGURASI PENTING (DIPERBARUI) ---
// PERBAIKAN: URL publik ke folder upload Anda
$BASE_URL = "http://localhost/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/"; 
// PERBAIKAN: Direktori upload fisik (naik 1 level ke Mobile, lalu masuk Uploads)
$UPLOAD_DIR = '../Uploads/Menu/'; 
// --- SELESAI KONFIGURASI ---

// Pastikan folder upload ada
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nama_menu = $_POST['nama_menu'];
    $deskripsi = $_POST['deskripsi'];
    $id_kategori = $_POST['id_kategori'];
    $gambar_url = null;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        
        $newFileName = optimizeAndSaveImage($_FILES['gambar'], $UPLOAD_DIR);
        
        if ($newFileName) {
            $gambar_url = $BASE_URL . $newFileName;
        } else {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message'] = 'Gagal mengupload atau mengoptimalkan gambar.';
            header("Location: add.php");
            exit;
        }
    } else {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message'] = 'Gambar wajib diisi.';
        header("Location: add.php");
        exit;
    }

    // Query (Prepared Statements)
    $sql = "INSERT INTO menu (nama_menu, deskripsi, id_kategori, gambar_url) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssis", $nama_menu, $deskripsi, $id_kategori, $gambar_url);
        
        if ($stmt->execute()) {
            $_SESSION['message_type'] = 'success';
            $_SESSION['message'] = 'Menu baru berhasil ditambahkan!';
        } else {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message'] = 'Gagal menyimpan data: ' . $stmt->error;
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