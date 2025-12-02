<?php
session_start();
include("../../../../Koneksi/koneksi.php");

if (isset($_GET['id']) && isset($_GET['id_produk'])) {
    $id_galeri = (int)$_GET['id'];
    $id_produk = (int)$_GET['id_produk'];

    // 1. Ambil Path Gambar
    $q = $conn->query("SELECT gambar_url FROM produk_galeri WHERE id_galeri = '$id_galeri'");
    if ($row = $q->fetch_assoc()) {
        $file_name = basename($row['gambar_url']);
        $file_path = dirname(__DIR__, 3) . '/assets/img/produk/' . $file_name;

        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // 2. Hapus DB
    $conn->query("DELETE FROM produk_galeri WHERE id_galeri = '$id_galeri'");

    $_SESSION['message'] = "Foto berhasil dihapus.";
    $_SESSION['message_type'] = "success";

    header("Location: ../update.php?id=" . $id_produk);
} else {
    header("Location: ../index.php");
}
