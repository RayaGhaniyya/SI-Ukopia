<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");

// Cek jika metode adalah GET (dari link <a>)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Perintah TRUNCATE akan menghapus semua data di tabel SANGAT CEPAT
    // dan me-reset AUTO_INCREMENT.
    $query = "TRUNCATE TABLE reservasi_arsip";

    if ($conn->query($query) === TRUE) {
        $_SESSION['message'] = "Semua riwayat arsip berhasil dihapus permanen.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus semua riwayat: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "Metode tidak diizinkan.";
    $_SESSION['message_type'] = "error";
}

// Redirect kembali ke halaman riwayat
header('Location: ../riwayat.php');
exit;
