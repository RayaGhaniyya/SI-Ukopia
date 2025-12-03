<?php
session_start();
include("../../../../Koneksi/koneksi.php"); 


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
        return ['success' => false, 'message' => 'Error: Format file tidak didukung.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $current_host = $_SERVER['HTTP_HOST'];

        
        $nama_produk = $_POST['nama_produk'];
        $id_kategori = (int)$_POST['id_kategori'];
        $deskripsi = $_POST['deskripsi'] ?? '';

        
        $origin = $_POST['origin'] ?? '';
        $altitude = $_POST['altitude'] ?? '';
        $variety = $_POST['variety'] ?? '';
        $process = $_POST['process'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $link = $_POST['link'] ?? '';

        
        $gambar_url = '';
        if (isset($_FILES['gambar_url']) && $_FILES['gambar_url']['error'] == 0) {
            $uploadResult = uploadGambar($_FILES['gambar_url'], $current_host);
            if ($uploadResult['success']) {
                $gambar_url = $uploadResult['url'];
            } else {
                throw new Exception($uploadResult['message']);
            }
        } else {
            throw new Exception("Error: Gambar utama wajib diisi.");
        }

        
        
        $stmt_produk = $conn->prepare("INSERT INTO produk (id_kategori, nama_produk, gambar_url, link, origin, altitude, notes, process, variety, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_produk->bind_param("isssssssss", $id_kategori, $nama_produk, $gambar_url, $link, $origin, $altitude, $notes, $process, $variety, $deskripsi);
        $stmt_produk->execute();
        $id_produk_baru = $conn->insert_id;
        $stmt_produk->close();

        
        if (isset($_FILES['galeri']) && !empty($_FILES['galeri']['name'][0])) {
            $files = $_FILES['galeri'];
            $count = count($files['name']);

            $stmt_galeri = $conn->prepare("INSERT INTO produk_galeri (id_produk, gambar_url) VALUES (?, ?)");

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] == 0) {
                    $file_single = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];

                    $res = uploadGambar($file_single, $current_host);
                    if ($res['success']) {
                        $url_galeri = $res['url'];
                        $stmt_galeri->bind_param("is", $id_produk_baru, $url_galeri);
                        $stmt_galeri->execute();
                    }
                }
            }
            $stmt_galeri->close();
        }

        
        $varian_sizes = $_POST['varian_size'];
        $varian_grinds = $_POST['varian_grind'];
        $varian_hargas = $_POST['varian_harga'];
        $varian_stoks = $_POST['varian_stok'];

        $stmt_varian = $conn->prepare("INSERT INTO detail_produk (id_produk, id_grind, id_size, stok, harga) VALUES (?, ?, ?, ?, ?)");
        for ($i = 0; $i < count($varian_sizes); $i++) {
            $id_size = (int)$varian_sizes[$i];
            
            $id_grind = (!empty($varian_grinds[$i]) && $varian_grinds[$i] !== 'N/A') ? (string)$varian_grinds[$i] : NULL;
            $harga = (int)$varian_hargas[$i];
            $stok = (int)$varian_stoks[$i];

            $stmt_varian->bind_param("isiii", $id_produk_baru, $id_grind, $id_size, $stok, $harga);
            $stmt_varian->execute();
        }
        $stmt_varian->close();

        $conn->commit();
        $_SESSION['message'] = "Produk berhasil ditambahkan.";
        $_SESSION['message_type'] = "success";
        header('Location: ../index.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal menyimpan: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../add.php');
        exit;
    }
}
