<?php
include("../../../../Koneksi/koneksi.php");
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../klaim.php"); exit;
}
$kode = trim($_POST['kode_unik']);
$query = "SELECT r.*, k.nama_reward, c.nama as nama_customer 
          FROM riwayat_reward r
          JOIN katalog_reward k ON r.id_reward = k.id_reward
          JOIN akun_customer c ON r.uid_customer = c.uid
          WHERE r.kode_unik = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kode);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    $_SESSION['message'] = "Kode Voucher TIDAK DITEMUKAN!";
    $_SESSION['message_type'] = "error";
    header("Location: ../klaim.php"); exit;
}
if ($data['status_klaim'] == 'Sudah Dipakai') {
    $_SESSION['message'] = "GAGAL! Voucher ini SUDAH DIPAKAI pada tanggal " . $data['tanggal_dapat'];
    $_SESSION['message_type'] = "error";
    header("Location: ../klaim.php"); exit;
}
$update = $conn->prepare("UPDATE riwayat_reward SET status_klaim = 'Sudah Dipakai' WHERE kode_unik = ?");
$update->bind_param("s", $kode);
if ($update->execute()) {
    $pesan_sukses = "BERHASIL! Voucher Valid.<br>";
    $pesan_sukses .= "Customer: <b>" . $data['nama_customer'] . "</b><br>";
    $pesan_sukses .= "Hadiah: <b>" . $data['nama_reward'] . "</b>";
    $_SESSION['message'] = $pesan_sukses;
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Terjadi kesalahan sistem.";
    $_SESSION['message_type'] = "error";
}
header("Location: ../klaim.php");
?>

