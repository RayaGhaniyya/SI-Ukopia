<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");

// Ambil parameter ID Kategori dari URL
$id_kategori = isset($_GET['id_kategori']) ? intval($_GET['id_kategori']) : 0;

// Base URL Gambar
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_image_url = "$protocol://$host/si-ukopia/BackOffice/List_Data/Uploads/Alat/";

try {
    if ($id_kategori <= 0) {
        throw new Exception("ID Kategori tidak valid");
    }

    $stmt = $conn->prepare("SELECT id_alat, nama_alat, gambar FROM alat WHERE id_kategori_alat = ? ORDER BY nama_alat ASC");
    $stmt->bind_param("i", $id_kategori);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $list_alat = [];
    while ($row = $result->fetch_assoc()) {
        // Handle Gambar
        $gambar_url = !empty($row['gambar']) 
            ? $base_image_url . $row['gambar'] 
            : $base_image_url . 'default.png';

        $list_alat[] = [
            'id_alat' => intval($row['id_alat']),
            'nama_alat' => $row['nama_alat'],
            'gambar' => $gambar_url,
            // Kamu bisa menambahkan 'deskripsi' jika di tabel alat ada kolom detail clicks dll
            // 'detail' => $row['deskripsi_alat'] 
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Daftar alat berhasil diambil',
        'data' => $list_alat
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>