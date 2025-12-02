<?php
// Sesuaikan path ke koneksi
include("../../../Koneksi/koneksi.php");

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

try {
    // Ambil semua data dari tabel kategori_menu
    $query = "SELECT * FROM kategori_menu ORDER BY nama_kategori ASC";
    $result = $conn->query($query);

    $data = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Rapikan tipe data
            $item = [
                'id_kategori_menu' => intval($row['id_kategori_menu']),
                'nama_kategori'    => $row['nama_kategori']
            ];

            // Jika kolom 'biji' ada (untuk logika show/hide input beans), sertakan juga
            if (isset($row['biji'])) {
                $item['biji'] = intval($row['biji']); // 1 = butuh beans, 0 = tidak
            }

            $data[] = $item;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data kategori menu berhasil diambil',
        'data'    => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>