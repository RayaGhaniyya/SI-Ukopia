<?php
include("../../../Koneksi/koneksi.php");

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

$uid_customer = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid_customer <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'UID Customer tidak valid'
    ]);
    exit;
}

try {
    $query = "SELECT 
                r.id_riwayat, 
                r.tanggal_dapat, 
                r.status_klaim, 
                r.kode_unik, 
                k.nama_reward, 
                k.deskripsi
              FROM riwayat_reward r
              JOIN katalog_reward k ON r.id_reward = k.id_reward
              WHERE r.uid_customer = ?
              ORDER BY r.tanggal_dapat DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $uid_customer);
    $stmt->execute();
    $result = $stmt->get_result();

    $history_list = [];
    while ($row = $result->fetch_assoc()) {
        $history_list[] = [
            'id_riwayat'    => intval($row['id_riwayat']),
            'nama_reward'   => $row['nama_reward'],
            'deskripsi'     => $row['deskripsi'], // Bisa null
            'tanggal_dapat' => $row['tanggal_dapat'],
            'status_klaim'  => $row['status_klaim'], // 'Belum Dipakai' / 'Sudah Dipakai'
            'kode_unik'     => $row['kode_unik'] // Kode voucher
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data riwayat reward berhasil diambil',
        'data'    => $history_list
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
