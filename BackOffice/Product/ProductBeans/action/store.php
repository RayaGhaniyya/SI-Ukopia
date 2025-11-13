<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");

// --- (Fungsi uploadGambar SAMA, tidak berubah) ---
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
// --- (AKHIR FUNGSI UPLOAD) ---


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $conn->begin_transaction();

    try {
        // (Langkah 1, 2, 3, 4, 5 SAMA PERSIS)
        $nama_produk = $_POST['nama_produk'];
        $id_kategori = (int)$_POST['id_kategori'];
        $deskripsi = $_POST['deskripsi'] ?? null;
        $origin = $_POST['origin'] ?? null;
        $altitude = $_POST['altitude'] ?? null;
        $variety = $_POST['variety'] ?? null;
        $process = $_POST['process'] ?? null;
        $notes = $_POST['notes'] ?? null;
        $link = $_POST['link'] ?? null;
        if (empty($_POST['varian_size'])) {
            throw new Exception("Minimal harus ada 1 varian produk.");
        }
        $varian_sizes = $_POST['varian_size'];
        $varian_grinds = $_POST['varian_grind'];
        $varian_hargas = $_POST['varian_harga'];
        $varian_stoks = $_POST['varian_stok'];
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
        $stmt_produk = $conn->prepare("INSERT INTO produk (id_kategori, nama_produk, gambar_url, link, origin, altitude, notes, process, variety, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_produk->bind_param("isssssssss", $id_kategori, $nama_produk, $gambar_url, $link, $origin, $altitude, $notes, $process, $variety, $deskripsi);
        $stmt_produk->execute();
        $id_produk_baru = $conn->insert_id;
        if ($id_produk_baru == 0) {
            throw new Exception("Gagal mendapatkan ID produk baru.");
        }
        $stmt_produk->close();
        // --- (AKHIR LANGKAH 1-5) ---


        // 6. Simpan ke tabel 'detail_produk' (Varian)
        $stmt_varian = $conn->prepare(
            "INSERT INTO detail_produk (id_produk, id_grind, id_size, stok, harga) 
             VALUES (?, ?, ?, ?, ?)"
        );

        for ($i = 0; $i < count($varian_sizes); $i++) {
            $id_size = (int)$varian_sizes[$i];

            // VVVVV--- PERBAIKAN DI SINI ---VVVVV
            // Jika $varian_grinds[$i] kosong (""), set $id_grind menjadi NULL
            // (Kita ubah jadi string agar 'bind_param' tipe 's' bisa kirim NULL)
            $id_grind = !empty($varian_grinds[$i]) ? (string)$varian_grinds[$i] : NULL;
            // ^^^^^--- SELESAI PERBAIKAN ---^^^^^

            $harga = (int)$varian_hargas[$i];
            $stok = (int)$varian_stoks[$i];

            // VVVVV--- PERBAIKAN DI SINI ---VVVVV
            // Ganti tipe data "iiiii" -> "iSiii" (id_grind = s)
            $stmt_varian->bind_param("isiii", $id_produk_baru, $id_grind, $id_size, $stok, $harga);
            // ^^^^^--- SELESAI PERBAIKAN ---^^^^^

            $stmt_varian->execute();
        }
        $stmt_varian->close();

        // 7. Commit
        $conn->commit();

        $_SESSION['message'] = "Produk Merchandise baru berhasil ditambahkan.";
        $_SESSION['message_type'] = "success";
        header('Location: ../index.php');
        exit;
    } catch (Exception $e) {
        // (Error handling sama)
        $conn->rollback();
        $_SESSION['message'] = "Gagal menyimpan produk: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../add.php');
        exit;
    }
} else {
    // (Redirect sama)
    $_SESSION['message'] = "Metode tidak diizinkan.";
    $_SESSION['message_type'] = "error";
    header('Location: ../index.php');
    exit;
}
