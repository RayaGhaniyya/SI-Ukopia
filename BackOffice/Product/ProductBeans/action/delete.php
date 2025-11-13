<?php
include("../../../../Koneksi/koneksi.php");
include("../../../Component/session.php");

if (isset($_GET['id'])) {

    $id_produk = $_GET['id'];

    // --- LANGKAH 1: Ambil URL Gambar SEBELUM Dihapus ---
    $file_path = '';
    $stmt_get = $conn->prepare("SELECT gambar_url FROM produk WHERE id_produk = ?");
    $stmt_get->bind_param("i", $id_produk);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $gambar_url = $row['gambar_url'];

        // --- LANGKAH 2: Konversi URL ke Path Fisik Server ---
        if (!empty($gambar_url)) {
            // Ambil nama file dari URL
            $file_name = basename($gambar_url); // Cth: 'produk_12345.jpg'

            // Buat path fisik (sama seperti di 'store.php')
            // 3x dirname() untuk naik dari '/action' -> '/ProductBeans' -> '/Product' -> '/BackOffice'
            $file_path = dirname(__DIR__, 3) . '/assets/img/produk/' . $file_name;
        }
    }
    $stmt_get->close();
    // --- Selesai Ambil Path ---


    // --- LANGKAH 3: Hapus Data dari Database (Pakai Transaksi) ---
    $conn->begin_transaction();

    try {
        // Hapus data dari tabel 'produk'
        // (ON DELETE CASCADE di DB kamu akan otomatis hapus data di 'detail_produk')
        $stmt_delete = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
        $stmt_delete->bind_param("i", $id_produk);
        $stmt_delete->execute();
        $stmt_delete->close();

        // --- LANGKAH 4: Hapus File Gambar Fisik ---
        if (!empty($file_path) && file_exists($file_path)) {
            unlink($file_path); // Hapus file dari folder assets/img/produk
        }

        // Jika semua berhasil
        $conn->commit();
        $_SESSION['message'] = "Produk dan gambar terkait berhasil dihapus.";
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

// Redirect kembali ke halaman index
header('Location: ../index.php');
exit;
