<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

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

header('Location: ../riwayat.php');
exit;

