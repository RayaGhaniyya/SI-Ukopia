<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_reservasi']) && isset($_POST['status'])) {

        $id_reservasi = $_POST['id_reservasi'];
        $new_status = $_POST['status'];

        if ($new_status == 'Confirmed' || $new_status == 'Cancelled') {

            $stmt = $conn->prepare("UPDATE reservasi SET status = ? WHERE id_reservasi = ?");
            $stmt->bind_param("si", $new_status, $id_reservasi);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Status reservasi berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal memperbarui status: " . $stmt->error;
                $_SESSION['message_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Status tidak valid.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Data tidak lengkap.";
        $_SESSION['message_type'] = "error";
    }
}

header('Location: ../index.php');
exit;

