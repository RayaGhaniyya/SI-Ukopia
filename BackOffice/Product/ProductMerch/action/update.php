<?php
session_start();
include("../../../../Koneksi/koneksi.php"); 


function uploadGambar($file, $current_host)
{
    $uploadDir_relative = dirname(__DIR__, 3) . '/assets/img/produk/';
    $uploadUrl = 'http://' . $current_host . '/SI-Ukopia/BackOffice/assets/img/produk/';

    if (!is_dir($uploadDir_relative)) mkdir($uploadDir_relative, 0755, true);

    $fileName = uniqid('produk_') . '_' . basename($file['name']);
    $targetFilePath = $uploadDir_relative . $fileName;
    $targetFileUrl = $uploadUrl . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'webp');

    if (in_array(strtolower($fileType), $allowTypes)) {
        if ($file['size'] > 5000000) return ['success' => false, 'message' => 'Error: Max 5MB.'];
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) return ['success' => true, 'url' => $targetFileUrl];
        else return ['success' => false, 'message' => 'Error: Gagal upload.'];
    } else return ['success' => false, 'message' => 'Error: Format salah.'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $current_host = $_SERVER['HTTP_HOST'];

        
        $id_produk = $_POST['id_produk'];
        $nama_produk = $_POST['nama_produk'];
        $deskripsi = $_POST['deskripsi'] ?? '';

        
        $origin = $_POST['origin'] ?? '';
        $altitude = $_POST['altitude'] ?? '';
        $process = $_POST['process'] ?? '';
        $variety = $_POST['variety'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $link = $_POST['link'] ?? '';

        
        $sql_update_img = "";
        $params_img = [];
        $types_img = "";

        if (isset($_FILES['gambar_url']) && $_FILES['gambar_url']['error'] == 0) {
            $upload = uploadGambar($_FILES['gambar_url'], $current_host);
            if ($upload['success']) {
                
                $qOld = $conn->query("SELECT gambar_url FROM produk WHERE id_produk = '$id_produk'");
                $dOld = $qOld->fetch_assoc();
                if ($dOld && !empty($dOld['gambar_url'])) {
                    $oldFile = basename($dOld['gambar_url']);
                    $oldPath = dirname(__DIR__, 3) . '/assets/img/produk/' . $oldFile;
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                $sql_update_img = ", gambar_url = ?";
                $params_img[] = $upload['url'];
                $types_img .= "s";
            } else {
                throw new Exception($upload['message']);
            }
        }

        
        $sql_produk = "UPDATE produk SET nama_produk=?, deskripsi=?, origin=?, altitude=?, process=?, variety=?, notes=?, link=? $sql_update_img WHERE id_produk=?";
        $stmt = $conn->prepare($sql_produk);

        $bind_types = "ssssssss" . $types_img . "i";
        $bind_params = array_merge([$nama_produk, $deskripsi, $origin, $altitude, $process, $variety, $notes, $link], $params_img, [$id_produk]);

        $stmt->bind_param($bind_types, ...$bind_params);
        $stmt->execute();
        $stmt->close();

        
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
                        $stmt_galeri->bind_param("is", $id_produk, $res['url']);
                        $stmt_galeri->execute();
                    }
                }
            }
            $stmt_galeri->close();
        }

        
        if (isset($_POST['varian_id'])) {
            $varian_ids = $_POST['varian_id'];
            $varian_sizes = $_POST['varian_size'];
            $varian_grinds = $_POST['varian_grind'];
            $varian_hargas = $_POST['varian_harga'];
            $varian_stoks = $_POST['varian_stok'];

            $stmt_update_var = $conn->prepare("UPDATE detail_produk SET id_size=?, id_grind=?, harga=?, stok=? WHERE id_detail_produk=?");
            $stmt_insert_var = $conn->prepare("INSERT INTO detail_produk (id_produk, id_size, id_grind, harga, stok) VALUES (?, ?, ?, ?, ?)");

            for ($i = 0; $i < count($varian_ids); $i++) {
                $v_id = $varian_ids[$i];
                $v_size = (int)$varian_sizes[$i];
                $v_grind = (!empty($varian_grinds[$i]) && $varian_grinds[$i] !== 'N/A') ? (string)$varian_grinds[$i] : NULL;
                $v_harga = (int)$varian_hargas[$i];
                $v_stok = (int)$varian_stoks[$i];

                if ($v_id === 'new') {
                    $stmt_insert_var->bind_param("iisii", $id_produk, $v_size, $v_grind, $v_harga, $v_stok);
                    $stmt_insert_var->execute();
                } else {
                    $v_id_int = (int)$v_id;
                    $stmt_update_var->bind_param("isiii", $v_size, $v_grind, $v_harga, $v_stok, $v_id_int);
                    $stmt_update_var->execute();
                }
            }
            $stmt_update_var->close();
            $stmt_insert_var->close();
        }

        
        if (!empty($_POST['delete_variants'])) {
            $ids_to_delete = explode(',', $_POST['delete_variants']);
            $ids_to_delete = array_filter($ids_to_delete);
            if (!empty($ids_to_delete)) {
                $ids_string = implode(',', array_map('intval', $ids_to_delete));
                $conn->query("DELETE FROM detail_produk WHERE id_detail_produk IN ($ids_string)");
            }
        }

        $conn->commit();
        $_SESSION['message'] = "Produk berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
        header('Location: ../index.php'); 
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal update: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../update.php?id=' . $_POST['id_produk']);
        exit;
    }
}
