<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Query ini menggunakan LEFT JOIN dan GROUP BY untuk menghitung jumlah alat per kategori
    $sql = "SELECT 
                k.id_kategori_alat, 
                k.nama_kategori_alat, 
                COUNT(a.id_alat) as jumlah_item
            FROM kategori_alat k
            LEFT JOIN alat a ON k.id_kategori_alat = a.id_kategori_alat
            GROUP BY k.id_kategori_alat, k.nama_kategori_alat
            ORDER BY k.nama_kategori_alat ASC";

    $result = $conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id_kategori' => intval($row['id_kategori_alat']),
            'nama_kategori' => $row['nama_kategori_alat'],
            'jumlah' => intval($row['jumlah_item']) // Angka "11", "7", "4" dst
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data kategori berhasil diambil',
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>