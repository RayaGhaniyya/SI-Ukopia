<?php
session_start();
// Path naik 4 tingkat: action -> Transaksi -> BackOffice -> SI-Ukopia -> Koneksi
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

// Cek Login Admin
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi habis']);
    exit;
}

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

if ($id == 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Update Status
$sql = "UPDATE transaksi SET status_pesanan = ? WHERE id_transaksi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Jika Batal, Kembalikan Stok
    if ($status == 'Batal') {
        $qItems = mysqli_query($conn, "SELECT id_detail_produk, jumlah FROM detail_transaksi WHERE id_transaksi = '$id'");
        while ($item = mysqli_fetch_assoc($qItems)) {
            $conn->query("UPDATE detail_produk SET stok = stok + {$item['jumlah']} WHERE id_detail_produk = {$item['id_detail_produk']}");
        }
    }

    echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal update database']);
}
