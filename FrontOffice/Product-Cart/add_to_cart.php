<?php
session_start();
include("../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!']);
    exit;
}
$uid_akun = $_SESSION['customer_uid'];
$input = json_decode(file_get_contents('php://input'), true);
$id_detail_produk = isset($input['id_detail_produk']) ? intval($input['id_detail_produk']) : 0;
$qty_baru = isset($input['qty']) ? intval($input['qty']) : 1;
if ($id_detail_produk == 0 || $qty_baru <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data produk tidak valid.']);
    exit;
}
$queryStok = mysqli_query($conn, "SELECT stok FROM detail_produk WHERE id_detail_produk = '$id_detail_produk'");
$dataProduk = mysqli_fetch_assoc($queryStok);
$stok_tersedia = intval($dataProduk['stok']);
$queryCart = mysqli_query($conn, "SELECT jumlah FROM keranjang WHERE uid_akun = '$uid_akun' AND id_detail_produk = '$id_detail_produk'");
$dataCart = mysqli_fetch_assoc($queryCart);
$qty_di_keranjang = $dataCart ? intval($dataCart['jumlah']) : 0;
$total_akan_datang = $qty_di_keranjang + $qty_baru;
if ($total_akan_datang > $stok_tersedia) {
    $sisa_bisa_diambil = $stok_tersedia - $qty_di_keranjang;
    if ($sisa_bisa_diambil <= 0) {
        $pesan = "Stok habis! Kamu sudah punya semua stok ($stok_tersedia item) di keranjangmu.";
    } else {
        $pesan = "Stok tidak cukup! Kamu sudah punya $qty_di_keranjang di keranjang. Sisa yang bisa ditambah cuma $sisa_bisa_diambil item.";
    }
    echo json_encode(['status' => 'error', 'message' => $pesan]);
    exit;
}
if ($qty_di_keranjang > 0) {
    $update = mysqli_query($conn, "UPDATE keranjang SET jumlah = jumlah + $qty_baru WHERE uid_akun = '$uid_akun' AND id_detail_produk = '$id_detail_produk'");
    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Jumlah produk di keranjang diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update keranjang.']);
    }
} else {
    $insert = mysqli_query($conn, "INSERT INTO keranjang (uid_akun, id_detail_produk, jumlah) VALUES ('$uid_akun', '$id_detail_produk', '$qty_baru')");
    if ($insert) {
        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil masuk keranjang!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke keranjang.']);
    }
}

