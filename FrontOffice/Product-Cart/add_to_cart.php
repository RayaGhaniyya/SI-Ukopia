<?php
session_start();
include("../../Koneksi/koneksi.php"); // Pastikan path koneksi benar

header('Content-Type: application/json'); // Agar outputnya JSON untuk JS

// 1. Cek Login
if (!isset($_SESSION['customer_uid'])) {
    // Kirim pesan error khusus yang nanti dideteksi JS untuk redirect login
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!']);
    exit;
}

// 2. Terima Data dari JavaScript
$input = json_decode(file_get_contents('php://input'), true);

$uid_akun = $_SESSION['customer_uid'];
$id_detail_produk = isset($input['id_detail_produk']) ? intval($input['id_detail_produk']) : 0;
$jumlah = isset($input['qty']) ? intval($input['qty']) : 1;

// Validasi data dasar
if ($id_detail_produk == 0 || $jumlah <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data produk tidak valid.']);
    exit;
}

// 3. Cek Stok Dulu (Anti Jebol)
$queryStok = mysqli_query($conn, "SELECT stok FROM detail_produk WHERE id_detail_produk = '$id_detail_produk'");
$dataProduk = mysqli_fetch_assoc($queryStok);

if (!$dataProduk || $dataProduk['stok'] < $jumlah) {
    echo json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi!']);
    exit;
}

// 4. Cek Apakah Produk Sudah Ada di Keranjang User Ini?
$cekKeranjang = mysqli_query($conn, "SELECT * FROM keranjang WHERE uid_akun = '$uid_akun' AND id_detail_produk = '$id_detail_produk'");

if (mysqli_num_rows($cekKeranjang) > 0) {
    // Jika sudah ada, UPDATE jumlahnya (tambah yang baru ke yang lama)
    $update = mysqli_query($conn, "UPDATE keranjang SET jumlah = jumlah + $jumlah WHERE uid_akun = '$uid_akun' AND id_detail_produk = '$id_detail_produk'");
    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Jumlah produk di keranjang diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update keranjang: ' . mysqli_error($conn)]);
    }
} else {
    // Jika belum ada, INSERT baru
    $insert = mysqli_query($conn, "INSERT INTO keranjang (uid_akun, id_detail_produk, jumlah) VALUES ('$uid_akun', '$id_detail_produk', '$jumlah')");
    if ($insert) {
        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil masuk keranjang!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke keranjang: ' . mysqli_error($conn)]);
    }
}
