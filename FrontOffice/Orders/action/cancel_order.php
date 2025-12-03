<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$id_transaksi = $_POST['id_transaksi'] ?? 0;
$alasan = $_POST['alasan'] ?? 'Berubah pikiran';

if (!$id_transaksi) {
    echo json_encode(['status' => 'error', 'message' => 'ID Transaksi tidak valid.']);
    exit;
}

$query = mysqli_query($conn, "SELECT tanggal_pesan, status_pesanan FROM transaksi WHERE id_transaksi = '$id_transaksi' AND uid_customer = '$uid'");
$trx = mysqli_fetch_assoc($query);

if (!$trx) {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
    exit;
}

$valid_status = ['Menunggu Pembayaran', 'Sudah Dibayar']; 
if (!in_array($trx['status_pesanan'], $valid_status)) {
    echo json_encode(['status' => 'error', 'message' => 'Pesanan ini sudah tidak bisa dibatalkan.']);
    exit;
}

$waktu_pesan = strtotime($trx['tanggal_pesan']);
$waktu_sekarang = time();
$selisih_detik = $waktu_sekarang - $waktu_pesan;
$selisih_jam = $selisih_detik / 3600;

if ($selisih_jam < 1) {
    $qDetail = mysqli_query($conn, "SELECT id_detail_produk, jumlah FROM detail_transaksi WHERE id_transaksi = '$id_transaksi'");
    while ($item = mysqli_fetch_assoc($qDetail)) {
        $conn->query("UPDATE detail_produk SET stok = stok + {$item['jumlah']} WHERE id_detail_produk = {$item['id_detail_produk']}");
    }

    $update = mysqli_query($conn, "UPDATE transaksi SET status_pesanan = 'Batal', catatan_admin = 'Dibatalkan user (<1 jam)' WHERE id_transaksi = '$id_transaksi'");

    $pesan = "Pesanan berhasil dibatalkan.";
} else {
    $update = mysqli_query($conn, "UPDATE transaksi SET status_pesanan = 'Pengajuan Batal', catatan_pesanan = CONCAT(catatan_pesanan, ' | Alasan Batal: $alasan') WHERE id_transaksi = '$id_transaksi'");

    $pesan = "Permintaan pembatalan dikirim ke Admin.";
}

if ($update) {
    echo json_encode(['status' => 'success', 'message' => $pesan]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memproses pembatalan.']);
}
