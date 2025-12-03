<?php
session_start();

include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');


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


$sql = "UPDATE transaksi SET status_pesanan = ? WHERE id_transaksi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    
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
