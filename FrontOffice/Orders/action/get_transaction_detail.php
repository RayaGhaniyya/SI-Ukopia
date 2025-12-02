<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis']);
    exit;
}
$uid = $_SESSION['customer_uid'];
$id_transaksi = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_transaksi == 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID Transaksi tidak valid']);
    exit;
}
$queryMain = mysqli_query($conn, "
    SELECT t.*, 
           a.label_alamat, a.nama_penerima, a.no_telepon, a.alamat_lengkap, a.kota, a.provinsi, a.kode_pos
    FROM transaksi t
    JOIN alamat_customer a ON t.id_alamat_kirim = a.id_alamat
    WHERE t.id_transaksi = '$id_transaksi' AND t.uid_customer = '$uid'
");
$transaksi = mysqli_fetch_assoc($queryMain);
if (!$transaksi) {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan']);
    exit;
}
$queryItems = mysqli_query($conn, "
    SELECT dt.*, 
           p.nama_produk, p.gambar_url, 
           s.ukuran, g.nama_grind
    FROM detail_transaksi dt
    JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
    JOIN produk p ON dp.id_produk = p.id_produk
    JOIN size s ON dp.id_size = s.id_size
    LEFT JOIN grind_size g ON dp.id_grind = g.id_grind
    WHERE dt.id_transaksi = '$id_transaksi'
");
$items = [];
while ($row = mysqli_fetch_assoc($queryItems)) {
    $row['gambar_url'] = str_replace("localhost", $_SERVER['HTTP_HOST'], $row['gambar_url']);
    $items[] = $row;
}
echo json_encode([
    'status' => 'success',
    'transaksi' => $transaksi,
    'items' => $items
]);

