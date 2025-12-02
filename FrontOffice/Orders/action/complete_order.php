<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$id_transaksi = $_POST['id_transaksi'] ?? 0;

if (!$id_transaksi) {
    echo json_encode(['status' => 'error', 'message' => 'ID Transaksi tidak valid.']);
    exit;
}

// 1. Cek Validasi (Milik user ini & Statusnya harus 'Dikirim')
$check = mysqli_query($conn, "SELECT status_pesanan FROM transaksi WHERE id_transaksi = '$id_transaksi' AND uid_customer = '$uid'");
$trx = mysqli_fetch_assoc($check);

if (!$trx) {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
    exit;
}

if ($trx['status_pesanan'] !== 'Dikirim') {
    echo json_encode(['status' => 'error', 'message' => 'Pesanan belum dikirim atau sudah selesai.']);
    exit;
}

// 2. Update Status
$update = mysqli_query($conn, "UPDATE transaksi SET status_pesanan = 'Selesai' WHERE id_transaksi = '$id_transaksi'");

if ($update) {
    echo json_encode(['status' => 'success', 'message' => 'Terima kasih! Pesanan telah selesai.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status.']);
}
?>