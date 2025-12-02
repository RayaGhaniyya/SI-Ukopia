<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");

if (isset($_GET['id'])) {

    $id_reservasi = $_GET['id'];
    $conn->begin_transaction();

    try {
        $stmt_arsip = $conn->prepare("INSERT INTO reservasi_arsip SELECT * FROM reservasi WHERE id_reservasi = ?");
        $stmt_arsip->bind_param("i", $id_reservasi);
        $stmt_arsip->execute();
        $stmt_arsip->close();

        $stmt_delete = $conn->prepare("DELETE FROM reservasi WHERE id_reservasi = ?");
        $stmt_delete->bind_param("i", $id_reservasi);
        $stmt_delete->execute();
        $stmt_delete->close();

        $conn->commit();

        $_SESSION['message'] = "Reservasi berhasil di-arsip.";
        $_SESSION['message_type'] = "success";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['message'] = "Gagal meng-arsip data: " . $exception->getMessage();
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "ID Reservasi tidak ditemukan (GET).";
    $_SESSION['message_type'] = "error";
}

header('Location: ../index.php');
exit;

