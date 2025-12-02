<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");

if (isset($_GET['id'])) {

    $id_produk = $_GET['id'];
    $file_path_utama = '';
    $galeri_paths = [];

    $stmt_get = $conn->prepare("SELECT gambar_url FROM produk WHERE id_produk = ?");
    $stmt_get->bind_param("i", $id_produk);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['gambar_url'])) {
            $file_path_utama = dirname(__DIR__, 3) . '/assets/img/produk/' . basename($row['gambar_url']);
        }
    }
    $stmt_get->close();

    $stmt_galeri = $conn->prepare("SELECT gambar_url FROM produk_galeri WHERE id_produk = ?");
    $stmt_galeri->bind_param("i", $id_produk);
    $stmt_galeri->execute();
    $res_galeri = $stmt_galeri->get_result();
    while ($row_gal = $res_galeri->fetch_assoc()) {
        if (!empty($row_gal['gambar_url'])) {
            $galeri_paths[] = dirname(__DIR__, 3) . '/assets/img/gallery/' . basename($row_gal['gambar_url']);
        }
    }
    $stmt_galeri->close();


    $conn->begin_transaction();

    try {
        $query_keranjang = "DELETE FROM keranjang WHERE id_detail_produk IN (SELECT id_detail_produk FROM detail_produk WHERE id_produk = ?)";
        $stmt_k = $conn->prepare($query_keranjang);
        $stmt_k->bind_param("i", $id_produk);
        $stmt_k->execute();
        $stmt_k->close();

        $query_transaksi = "DELETE FROM detail_transaksi WHERE id_detail_produk IN (SELECT id_detail_produk FROM detail_produk WHERE id_produk = ?)";
        $stmt_t = $conn->prepare($query_transaksi);
        $stmt_t->bind_param("i", $id_produk);
        $stmt_t->execute();
        $stmt_t->close();

        $stmt_u = $conn->prepare("DELETE FROM ulasan_produk WHERE id_produk = ?");
        $stmt_u->bind_param("i", $id_produk);
        $stmt_u->execute();
        $stmt_u->close();

        $stmt_pg = $conn->prepare("DELETE FROM produk_galeri WHERE id_produk = ?");
        $stmt_pg->bind_param("i", $id_produk);
        $stmt_pg->execute();
        $stmt_pg->close();

        $stmt_delete = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
        $stmt_delete->bind_param("i", $id_produk);
        $stmt_delete->execute();
        $stmt_delete->close();


        if (!empty($file_path_utama) && file_exists($file_path_utama)) {
            unlink($file_path_utama);
        }
        foreach ($galeri_paths as $path) {
            if (!empty($path) && file_exists($path)) {
                unlink($path);
            }
        }

        $conn->commit();
        $_SESSION['message'] = "Produk berhasil di-Hard Delete (termasuk riwayat transaksinya).";
        $_SESSION['message_type'] = "success";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal Hard Delete: " . $exception->getMessage();
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "ID Produk tidak ditemukan.";
    $_SESSION['message_type'] = "error";
}

header('Location: ../index.php');
exit;

