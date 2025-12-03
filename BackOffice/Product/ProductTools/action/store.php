<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");


function uploadGambar($file, $current_host)
{
    
    $uploadDir_relative = dirname(__DIR__, 3) . '/assets/img/produk/';
    $uploadUrl = 'http://' . $current_host . '/SI-Ukopia/BackOffice/assets/img/produk/';

    if (!is_dir($uploadDir_relative)) {
        if (!mkdir($uploadDir_relative, 0755, true)) {
            return ['success' => false, 'message' => 'Error: Gagal membuat folder assets/img/produk.'];
        }
    }
    $fileName = uniqid('produk_') . '_' . basename($file['name']);
    $targetFilePath = $uploadDir_relative . $fileName;
    $targetFileUrl = $uploadUrl . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'webp');
    if (in_array(strtolower($fileType), $allowTypes)) {
        if ($file['size'] > 5000000) { 
            return ['success' => false, 'message' => 'Error: Ukuran file terlalu besar (Max 5MB).'];
        }
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return ['success' => true, 'url' => $targetFileUrl];
        } else {
            return ['success' => false, 'message' => 'Error: Gagal memindahkan file.'];
        }
    } else {
        return ['success' => false, 'message' => 'Error: Hanya format JPG, JPEG, PNG, & WEBP yang diizinkan.'];
    }
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    

    try {
        
        $nama_produk = $_POST['nama_produk'];
        $id_kategori = (int)$_POST['id_kategori']; 
        $deskripsi = $_POST['deskripsi'] ?? null;
        $link = $_POST['link'] ?? null;

        
        $origin = $_POST['origin'] ?? null;
        $altitude = $_POST['altitude'] ?? null;
        $variety = $_POST['variety'] ?? null;
        $process = $_POST['process'] ?? null;
        $notes = $_POST['notes'] ?? null;

        
        $gambar_url = '';
        if (isset($_FILES['gambar_url']) && $_FILES['gambar_url']['error'] == 0) {
            $current_host = $_SERVER['HTTP_HOST'];
            $uploadResult = uploadGambar($_FILES['gambar_url'], $current_host);
            if ($uploadResult['success']) {
                $gambar_url = $uploadResult['url'];
            } else {
                throw new Exception($uploadResult['message']);
            }
        } else {
            throw new Exception("Error: Gambar utama wajib diisi.");
        }

        
        
        $stmt_produk = $conn->prepare(
            "INSERT INTO produk (id_kategori, nama_produk, gambar_url, link, origin, altitude, notes, process, variety, deskripsi) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_produk->bind_param(
            "isssssssss",
            $id_kategori,
            $nama_produk,
            $gambar_url,
            $link,
            $origin,
            $altitude,
            $notes,
            $process,
            $variety,
            $deskripsi
        );

        if ($stmt_produk->execute()) {
            $_SESSION['message'] = "Produk (Alat/Rekomendasi) baru berhasil ditambahkan.";
            $_SESSION['message_type'] = "success";
            header('Location: ../index.php');
            exit;
        } else {
            throw new Exception("Gagal menyimpan ke database: " . $stmt_produk->error);
        }
        $stmt_produk->close();
    } catch (Exception $e) {
        
        $_SESSION['message'] = "Gagal menyimpan produk: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../add.php'); 
        exit;
    }
} else {
    $_SESSION['message'] = "Metode tidak diizinkan.";
    $_SESSION['message_type'] = "error";
    header('Location: ../index.php');
    exit;
}
