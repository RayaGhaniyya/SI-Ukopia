<?php
// COBA GANTI JADI SATU TITIK DULU (../)
include("../../../Koneksi/koneksi.php"); 

// Atau sesuaikan dengan file add.php kamu (yang pakai ../../../)
// Kalau add.php pakai ../../../ berarti path koneksi kamu sangat jauh di luar folder BackOffice?
// Pastikan path ini valid. Cek error_log PHP jika ragu.

$id_kategori = isset($_GET['id_kategori']) ? intval($_GET['id_kategori']) : 0;

// Header JSON wajib ditaruh di awal sebelum ada output lain
header('Content-Type: application/json');

if ($id_kategori > 0) {
    $stmt = $conn->prepare("SELECT id_menu, nama_menu FROM menu WHERE id_kategori = ? ORDER BY nama_menu ASC");
    $stmt->bind_param("i", $id_kategori);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $menus = [];
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
        echo json_encode($menus);
    } else {
        // Kirim array kosong jika gagal execute
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>