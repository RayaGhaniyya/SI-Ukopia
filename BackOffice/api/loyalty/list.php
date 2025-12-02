<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'pending'; // 'pending' atau 'history'

if ($uid <= 0) {
    exit(json_encode(['success' => false, 'message' => 'UID wajib diisi']));
}

try {
    $sql = "SELECT 
                l.id_loyalty, 
                l.tanggal, 
                l.biji_kopi,
                l.keasaman, l.kepahitan, l.aroma, l.kemanisan, l.kekentalan, l.catatan,
                m.nama_menu,
                k.nama_kategori
            FROM loyalty l
            JOIN menu m ON l.id_menu = m.id_menu
            JOIN kategori_menu k ON l.id_kategori = k.id_kategori_menu
            WHERE l.uid_akun = ?";

    if ($type == 'pending') {
        $sql .= " AND l.status_pengisian = 'Menunggu Review'";
    } else {
        $sql .= " AND l.status_pengisian = 'Selesai'";
    }
    
    $sql .= " ORDER BY l.tanggal DESC, l.id_loyalty DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id_loyalty'   => intval($row['id_loyalty']),
            'nama_menu'    => $row['nama_menu'],
            'kategori'     => $row['nama_kategori'],
            'biji_kopi'    => $row['biji_kopi'] ?: '-', // Jika null ganti dash
            'tanggal'      => $row['tanggal'],
            'nilai' => [
                'keasaman'   => intval($row['keasaman']),
                'kepahitan'  => intval($row['kepahitan']),
                'aroma'      => intval($row['aroma']),
                'kemanisan'  => intval($row['kemanisan']),
                'kekentalan' => intval($row['kekentalan']),
                'catatan'    => $row['catatan'] ?: ''
            ]
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
