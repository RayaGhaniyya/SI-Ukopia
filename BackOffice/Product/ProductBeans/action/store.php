<?php
session_start();
include("../../../../Koneksi/koneksi.php");

// --- FUNGSI UPLOAD ---
function uploadGambar($file, $current_host)
{
    $uploadDir = dirname(__DIR__, 3) . '/assets/img/produk/';
    $uploadUrl = 'http://' . $current_host . '/SI-Ukopia/BackOffice/assets/img/produk/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $fileName = uniqid('produk_') . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    $targetUrl = $uploadUrl . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) return ['success' => true, 'url' => $targetUrl];
    return ['success' => false, 'message' => 'Gagal upload.'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $current_host = $_SERVER['HTTP_HOST'];

        // Data Utama
        $nama = $_POST['nama_produk'];
        $kategori = (int)$_POST['id_kategori'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $origin = $_POST['origin'] ?? '';
        $altitude = $_POST['altitude'] ?? '';
        $process = $_POST['process'] ?? '';
        $variety = $_POST['variety'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $link = ''; // Beans tidak pakai link eksternal

        // 1. Upload Gambar Utama
        if (!isset($_FILES['gambar_url']) || $_FILES['gambar_url']['error'] != 0) throw new Exception("Gambar utama wajib diisi.");
        $res = uploadGambar($_FILES['gambar_url'], $current_host);
        if (!$res['success']) throw new Exception($res['message']);
        $gambar_url = $res['url'];

        // 2. Insert Produk
        $stmt = $conn->prepare("INSERT INTO produk (id_kategori, nama_produk, gambar_url, link, origin, altitude, notes, process, variety, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssss", $kategori, $nama, $gambar_url, $link, $origin, $altitude, $notes, $process, $variety, $deskripsi);
        $stmt->execute();
        $id_produk = $conn->insert_id;
        $stmt->close();

        // 3. Upload Galeri (Multiple)
        if (isset($_FILES['galeri']) && !empty($_FILES['galeri']['name'][0])) {
            $files = $_FILES['galeri'];
            $count = count($files['name']);
            $stmt_g = $conn->prepare("INSERT INTO produk_galeri (id_produk, gambar_url) VALUES (?, ?)");

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
                        $stmt_g->bind_param("is", $id_produk, $res['url']);
                        $stmt_g->execute();
                    }
                }
            }
            $stmt_g->close();
        }

        // 4. Insert Varian
        $stmt_v = $conn->prepare("INSERT INTO detail_produk (id_produk, id_grind, id_size, stok, harga) VALUES (?, ?, ?, ?, ?)");
        $sizes = $_POST['varian_size'];
        $grinds = $_POST['varian_grind'];
        $hargas = $_POST['varian_harga'];
        $stoks = $_POST['varian_stok'];

        for ($i = 0; $i < count($sizes); $i++) {
            $sz = (int)$sizes[$i];
            $gr = !empty($grinds[$i]) ? (string)$grinds[$i] : NULL;
            $pr = (int)$hargas[$i];
            $st = (int)$stoks[$i];
            $stmt_v->bind_param("isiii", $id_produk, $gr, $sz, $st, $pr);
            $stmt_v->execute();
        }
        $stmt_v->close();

        $conn->commit();
        $_SESSION['message'] = "Produk Beans berhasil ditambahkan.";
        $_SESSION['message_type'] = "success";
        header('Location: ../index.php');
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../add.php');
    }
}
