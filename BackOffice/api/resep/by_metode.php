<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Ambil Parameter
$id_metode = isset($_GET['id_metode']) ? intval($_GET['id_metode']) : 0;
$type      = isset($_GET['type']) ? $_GET['type'] : 'all'; // 'all' atau 'my'
$uid       = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

try {
    // Validasi dasar
    if ($id_metode <= 0) {
        throw new Exception("ID Metode wajib diisi");
    }

    // Query Dasar (Hanya ambil kolom yang dibutuhkan untuk List View agar ringan)
    $sql = "SELECT 
                r.id_resep, 
                r.nama_resep, 
                r.deskripsi, 
                r.jumlah_air, 
                r.jumlah_kopi, 
                r.waktu_ekstraksi,
                u.nama AS nama_pembuat
            FROM resep r
            JOIN akun_customer u ON r.uid_akun = u.uid
            WHERE r.id_metode = ?";

    // Filter Tab: My Recipe vs All Recipe
    if ($type === 'my') {
        if ($uid <= 0) throw new Exception("UID wajib diisi untuk My Recipe");
        
        $sql .= " AND r.uid_akun = ? ORDER BY r.tanggal DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_metode, $uid);
    } else {
        // All Recipes (Bisa tambahkan logika public/private jika ada)
        $sql .= " ORDER BY r.tanggal DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_metode);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $resep_list = [];
    while ($row = $result->fetch_assoc()) {
        // Format Waktu agar cantik (Optional, misal 120 -> 2m 0s)
        // Tapi biarkan raw data (detik) agar Kotlin yang format "02:30"
        
        $resep_list[] = [
            'id_resep'        => intval($row['id_resep']),
            'nama_resep'      => $row['nama_resep'],
            'deskripsi'       => $row['deskripsi'], // Ditampilkan di bawah nama (misal: "enak")
            'jumlah_air'      => intval($row['jumlah_air']),
            'jumlah_kopi'     => intval($row['jumlah_kopi']),
            'waktu_ekstraksi' => intval($row['waktu_ekstraksi']),
            'nama_pembuat'    => $row['nama_pembuat']
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data resep berhasil diambil',
        'data'    => $resep_list
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>