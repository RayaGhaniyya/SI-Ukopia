<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");


function uploadGambar($file, $current_host)
{
    $uploadDir_relative = dirname(__DIR__, 3) . '/assets/img/produk/';
    $uploadUrl = 'http://' . $current_host . '/SI-Ukopia/BackOffice/assets/img/produk/';
    if (!is_dir($uploadDir_relative)) {
        mkdir($uploadDir_relative, 0755, true);
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
        $id_produk = $_POST['id_produk'];
        $nama_produk = $_POST['nama_produk'];
        $id_kategori = (int)$_POST['id_kategori'];
        $deskripsi = $_POST['deskripsi'] ?? null;
        $link = $_POST['link'] ?? null;
        $origin = $_POST['origin'] ?? null;
        $altitude = $_POST['altitude'] ?? null;
        $variety = $_POST['variety'] ?? null;
        $process = $_POST['process'] ?? null;
        $notes = $_POST['notes'] ?? null;

        $sql_gambar = "";
        $gambar_url_new = "";
        $old_file_path = "";

        if (isset($_FILES['gambar_url']) && $_FILES['gambar_url']['error'] == 0) {

            $stmt_get = $conn->prepare("SELECT gambar_url FROM produk WHERE id_produk = ?");
            $stmt_get->bind_param("i", $id_produk);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $file_name = basename($row['gambar_url']);
                $old_file_path = dirname(__DIR__, 3) . '/assets/img/produk/' . $file_name;
            }
            $stmt_get->close();

            $current_host = $_SERVER['HTTP_HOST'];
            $uploadResult = uploadGambar($_FILES['gambar_url'], $current_host);

            if ($uploadResult['success']) {
                $gambar_url_new = $uploadResult['url'];
                $sql_gambar = ", gambar_url = ?";
            } else {
                throw new Exception($uploadResult['message']);
            }
        }

        
        $query_produk = "
            UPDATE produk SET 
                id_kategori = ?, nama_produk = ?, deskripsi = ?, origin = ?, 
                altitude = ?, variety = ?, process = ?, notes = ?, link = ?
                $sql_gambar 
            WHERE id_produk = ?
        ";

        $stmt_produk = $conn->prepare($query_produk);

        if (!empty($sql_gambar)) {
            $stmt_produk->bind_param(
                "isssssssssi",
                $id_kategori,
                $nama_produk,
                $deskripsi,
                $origin,
                $altitude,
                $variety,
                $process,
                $notes,
                $link,
                $gambar_url_new,
                $id_produk
            );
        } else {
            $stmt_produk->bind_param(
                "issssssssi",
                $id_kategori,
                $nama_produk,
                $deskripsi,
                $origin,
                $altitude,
                $variety,
                $process,
                $notes,
                $link,
                $id_produk
            );
        }

        if ($stmt_produk->execute()) {
            
            if (!empty($old_file_path) && file_exists($old_file_path)) {
                unlink($old_file_path);
            }

            $_SESSION['message'] = "Produk (Alat/Rekomendasi) berhasil diperbarui.";
            $_SESSION['message_type'] = "success";
            header('Location: ../index.php?cache=' . time());
            exit;
        } else {
            throw new Exception("Gagal update database: " . $stmt_produk->error);
        }
        $stmt_produk->close();
    } catch (Exception $e) {
        $_SESSION['message'] = "Gagal memperbarui produk: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../update.php?id=' . $id_produk . '&cache=' . time());
        exit;
    }
} else {
    $_SESSION['message'] = "Metode tidak diizinkan.";
    $_SESSION['message_type'] = "error";
    header('Location: ../index.php?cache=' . time());
    exit;
}
