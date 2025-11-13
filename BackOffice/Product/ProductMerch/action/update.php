<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");

// --- (Fungsi uploadGambar SAMA, tidak berubah) ---
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
// --- (AKHIR FUNGSI UPLOAD) ---


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // (Langkah 1-5 SAMA PERSIS)
    $id_produk = $_POST['id_produk'];
    $nama_produk = $_POST['nama_produk'];
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = $_POST['deskripsi'] ?? null;
    $origin = $_POST['origin'] ?? null;
    $altitude = $_POST['altitude'] ?? null;
    $variety = $_POST['variety'] ?? null;
    $process = $_POST['process'] ?? null;
    $notes = $_POST['notes'] ?? null;
    $link = $_POST['link'] ?? null;
    $varian_ids = $_POST['varian_id'];
    $varian_sizes = $_POST['varian_size'];
    $varian_grinds = $_POST['varian_grind'];
    $varian_hargas = $_POST['varian_harga'];
    $varian_stoks = $_POST['varian_stok'];
    $delete_variants_str = $_POST['delete_variants'] ?? '';
    $delete_variant_ids = array_filter(explode(',', $delete_variants_str));
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
            $_SESSION['message'] = $uploadResult['message'];
            $_SESSION['message_type'] = "error";
            header('Location: ../update.php?id=' . $id_produk);
            exit;
        }
    }
    // --- (AKHIR LANGKAH 1-5) ---

    $conn->begin_transaction();
    try {
        // 7. Update tabel 'produk' (SAMA PERSIS)
        $query_produk = "UPDATE produk SET id_kategori = ?, nama_produk = ?, deskripsi = ?, origin = ?, altitude = ?, variety = ?, process = ?, notes = ?, link = ? $sql_gambar WHERE id_produk = ?";
        $stmt_produk = $conn->prepare($query_produk);
        if (!empty($sql_gambar)) {
            $stmt_produk->bind_param("isssssssssi", $id_kategori, $nama_produk, $deskripsi, $origin, $altitude, $variety, $process, $notes, $link, $gambar_url_new, $id_produk);
        } else {
            $stmt_produk->bind_param("issssssssi", $id_kategori, $nama_produk, $deskripsi, $origin, $altitude, $variety, $process, $notes, $link, $id_produk);
        }
        $stmt_produk->execute();
        $stmt_produk->close();

        // 8. Proses Varian
        $stmt_update_varian = $conn->prepare("UPDATE detail_produk SET id_size = ?, id_grind = ?, harga = ?, stok = ? WHERE id_detail_produk = ?");
        $stmt_insert_varian = $conn->prepare("INSERT INTO detail_produk (id_produk, id_size, id_grind, harga, stok) VALUES (?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($varian_ids); $i++) {
            $id_varian = $varian_ids[$i];
            $id_size = (int)$varian_sizes[$i];

            // VVVVV--- PERBAIKAN BUG 1 DI SINI ---VVVVV
            $id_grind = !empty($varian_grinds[$i]) ? $varian_grinds[$i] : NULL;
            $harga = (int)$varian_hargas[$i];
            $stok = (int)$varian_stoks[$i];

            if ($id_varian == 'new') {
                // Ganti tipe data "iiiii" -> "isiii"
                $stmt_insert_varian->bind_param("isiii", $id_produk, $id_grind, $id_size, $harga, $stok);
                $stmt_insert_varian->execute();
            } else {
                // Ganti tipe data "iiiii" -> "isiii"
                $stmt_update_varian->bind_param("isiii", $id_size, $id_grind, $harga, $stok, $id_varian);
                $stmt_update_varian->execute();
            }
            // ^^^^^--- SELESAI PERBAIKAN ---^^^^^
        }
        $stmt_update_varian->close();
        $stmt_insert_varian->close();

        // 9. Hapus Varian (SAMA PERSIS)
        if (!empty($delete_variant_ids)) {
            $placeholders = implode(',', array_fill(0, count($delete_variant_ids), '?'));
            $types = str_repeat('i', count($delete_variant_ids));
            $stmt_delete_varian = $conn->prepare("DELETE FROM detail_produk WHERE id_detail_produk IN ($placeholders)");
            $stmt_delete_varian->bind_param($types, ...$delete_variant_ids);
            $stmt_delete_varian->execute();
            $stmt_delete_varian->close();
        }

        // 10. Hapus file lama (SAMA PERSIS)
        if (!empty($old_file_path) && file_exists($old_file_path)) {
            unlink($old_file_path);
        }

        // 11. Commit
        $conn->commit();
        $_SESSION['message'] = "Produk Merchandise berhasil diperbarui.";
        $_SESSION['message_type'] = "success";
        header('Location: ../index.php?cache=' . time()); // <-- PERBAIKAN BUG 3
        exit;
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal memperbarui produk: " . $exception->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../update.php?id=' . $id_produk . '&cache=' . time()); // <-- PERBAIKAN BUG 3
        exit;
    }
} else {
    $_SESSION['message'] = "Metode tidak diizinkan.";
    $_SESSION['message_type'] = "error";
    header('Location: ../index.php?cache=' . time()); // <-- PERBAIKAN BUG 3
    exit;
}
