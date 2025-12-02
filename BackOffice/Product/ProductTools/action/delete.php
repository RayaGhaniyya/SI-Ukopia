<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");

if (isset($_GET['id'])) {

    $id_produk = $_GET['id'];

    $file_path = '';
    $stmt_get = $conn->prepare("SELECT gambar_url FROM produk WHERE id_produk = ?");
    $stmt_get->bind_param("i", $id_produk);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $gambar_url = $row['gambar_url'];

        if (!empty($gambar_url)) {
            $file_name = basename($gambar_url);

            $file_path = dirname(__DIR__, 3) . '/assets/img/produk/' . $file_name;
        }
    }
    $stmt_get->close();


    $conn->begin_transaction();

    try {
        $stmt_delete = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
        $stmt_delete->bind_param("i", $id_produk);
        $stmt_delete->execute();
        $stmt_delete->close();

        if (!empty($file_path) && file_exists($file_path)) {
            unlink($file_path); // Hapus file dari folder assets/img/produk
        }

        $conn->commit();
        $_SESSION['message'] = "Produk berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal menghapus produk: " . $exception->getMessage();
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "ID Produk tidak ditemukan.";
    $_SESSION['message_type'] = "error";
}

header('Location: ../index.php');
exit;

