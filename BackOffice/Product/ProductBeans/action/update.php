<?php
session_start();
include("../../../../Koneksi/koneksi.php");

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

        $id = $_POST['id_produk'];
        $nama = $_POST['nama_produk'];
        $kategori = (int)$_POST['id_kategori'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $origin = $_POST['origin'] ?? '';
        $altitude = $_POST['altitude'] ?? '';
        $process = $_POST['process'] ?? '';
        $variety = $_POST['variety'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $link = '';

        $sql_img = "";
        $params = [$kategori, $nama, $deskripsi, $origin, $altitude, $variety, $process, $notes, $link];
        $types = "issssssss";

        if (isset($_FILES['gambar_url']) && $_FILES['gambar_url']['error'] == 0) {
            $res = uploadGambar($_FILES['gambar_url'], $current_host);
            if ($res['success']) {
                $sql_img = ", gambar_url = ?";
                $params[] = $res['url'];
                $types .= "s";
            }
        }

        $params[] = $id;
        $types .= "i";

        $sql = "UPDATE produk SET id_kategori=?, nama_produk=?, deskripsi=?, origin=?, altitude=?, variety=?, process=?, notes=?, link=? $sql_img WHERE id_produk=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        if (isset($_FILES['galeri']) && !empty($_FILES['galeri']['name'][0])) {
            $files = $_FILES['galeri'];
            $count = count($files['name']);
            $stmt_g = $conn->prepare("INSERT INTO produk_galeri (id_produk, gambar_url) VALUES (?, ?)");
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] == 0) {
                    $f = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                    $res = uploadGambar($f, $current_host);
                    if ($res['success']) {
                        $stmt_g->bind_param("is", $id, $res['url']);
                        $stmt_g->execute();
                    }
                }
            }
            $stmt_g->close();
        }

        $stmt_upd = $conn->prepare("UPDATE detail_produk SET id_size=?, id_grind=?, harga=?, stok=? WHERE id_detail_produk=?");
        $stmt_ins = $conn->prepare("INSERT INTO detail_produk (id_produk, id_size, id_grind, harga, stok) VALUES (?, ?, ?, ?, ?)");

        $ids = $_POST['varian_id'];
        $sizes = $_POST['varian_size'];
        $grinds = $_POST['varian_grind'];
        $hargas = $_POST['varian_harga'];
        $stoks = $_POST['varian_stok'];

        for ($i = 0; $i < count($ids); $i++) {
            $v_id = $ids[$i];
            $sz = (int)$sizes[$i];
            $gr = !empty($grinds[$i]) ? (string)$grinds[$i] : NULL;
            $pr = (int)$hargas[$i];
            $st = (int)$stoks[$i];

            if ($v_id == 'new') {
                $stmt_ins->bind_param("isiii", $id, $sz, $gr, $pr, $st);
                $stmt_ins->execute();
            } else {
                $stmt_upd->bind_param("isiii", $sz, $gr, $pr, $st, $v_id);
                $stmt_upd->execute();
            }
        }
        $stmt_upd->close();
        $stmt_ins->close();

        if (!empty($_POST['delete_variants'])) {
            $del_ids = array_filter(explode(',', $_POST['delete_variants']));
            if (!empty($del_ids)) {
                $str_ids = implode(',', array_map('intval', $del_ids));
                $conn->query("DELETE FROM detail_produk WHERE id_detail_produk IN ($str_ids)");
            }
        }

        $conn->commit();
        $_SESSION['message'] = "Produk berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
        header('Location: ../index.php');
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal update: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header('Location: ../update.php?id=' . $_POST['id_produk']);
    }
}

