<?php
session_start();
include("../../Koneksi/koneksi.php");

// Agar respon selalu dianggap JSON
header('Content-Type: application/json');

// 1. Cek Login
if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis, silakan login kembali.']);
    exit;
}

// 2. Ambil Data JSON dari JavaScript
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';
$id_keranjang = isset($input['id_keranjang']) ? intval($input['id_keranjang']) : 0;
$uid = $_SESSION['customer_uid'];

// 3. Logika Update Qty
if ($action === 'update_qty') {
    $qty = intval($input['qty']);

    if ($qty > 0) {
        // Cek stok dulu sebelum update (Opsional tapi bagus)
        $cekStok = mysqli_query($conn, "
            SELECT dp.stok FROM keranjang k 
            JOIN detail_produk dp ON k.id_detail_produk = dp.id_detail_produk 
            WHERE k.id_keranjang = '$id_keranjang'
        ");
        $data = mysqli_fetch_assoc($cekStok);

        if ($data && $data['stok'] >= $qty) {
            $query = mysqli_query($conn, "UPDATE keranjang SET jumlah = '$qty' WHERE id_keranjang = '$id_keranjang' AND uid_akun = '$uid'");
            if ($query) {
                echo json_encode(['status' => 'success', 'message' => 'Jumlah berhasil diupdate']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Jumlah tidak valid']);
    }
}
// 4. Logika Hapus Item
elseif ($action === 'delete') {
    $query = mysqli_query($conn, "DELETE FROM keranjang WHERE id_keranjang = '$id_keranjang' AND uid_akun = '$uid'");

    if ($query) {
        echo json_encode(['status' => 'success', 'message' => 'Item dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus item']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
}
