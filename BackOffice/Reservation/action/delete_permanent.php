<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_reservasi'])) {

        $id_reservasi = $_POST['id_reservasi'];

        $stmt = $conn->prepare("DELETE FROM reservasi_arsip WHERE id_reservasi = ?");
        $stmt->bind_param("i", $id_reservasi);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Riwayat arsip berhasil dihapus permanen.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menghapus riwayat: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "ID Reservasi tidak ditemukan.";
        $_SESSION['message_type'] = "error";
    }
}

header('Location: ../riwayat.php');
exit;

