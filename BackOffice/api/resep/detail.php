<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

$id_resep = isset($_GET['id_resep']) ? intval($_GET['id_resep']) : 0;

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_img_alat = "$protocol://$host/si-ukopia/BackOffice/List_Data/Uploads/Alat/";

try {
    if ($id_resep <= 0) {
        throw new Exception("ID Resep tidak valid");
    }

    $sql = "SELECT 
                r.*, 
                m.nama_metode, 
                u.nama AS nama_pembuat
            FROM resep r
            LEFT JOIN metode m ON r.id_metode = m.id_metode
            LEFT JOIN akun_customer u ON r.uid_akun = u.uid
            WHERE r.id_resep = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_resep);
    $stmt->execute();
    $data_resep = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data_resep) {
        throw new Exception("Resep tidak ditemukan");
    }

    $ratio = 0;
    if ($data_resep['jumlah_kopi'] > 0) {
        $ratio = round($data_resep['jumlah_air'] / $data_resep['jumlah_kopi'], 1);
    }

    $sql_alat = "SELECT 
                    a.id_alat, 
                    a.nama_alat, 
                    a.gambar,
                    a.deskripsi_alat -- Misal info clicks/settingan ada di sini (jika ada)
                 FROM resep_detail_alat d
                 JOIN alat a ON d.id_alat = a.id_alat
                 WHERE d.id_resep = ?";
                 
    $stmt_alat = $conn->prepare($sql_alat);
    $stmt_alat->bind_param("i", $id_resep);
    $stmt_alat->execute();
    $res_alat = $stmt_alat->get_result();
    
    $list_alat = [];
    while ($row = $res_alat->fetch_assoc()) {
        $gambar = !empty($row['gambar']) ? $base_img_alat . $row['gambar'] : $base_img_alat . 'default.png';
        
        $list_alat[] = [
            'id_alat'   => intval($row['id_alat']),
            'nama_alat' => $row['nama_alat'],
            'gambar'    => $gambar,
            'setting'   => $row['deskripsi_alat'] // Opsional: Info tambahan alat
        ];
    }
    $stmt_alat->close();

    $response_data = [
        'id_resep'        => intval($data_resep['id_resep']),
        'nama_resep'      => $data_resep['nama_resep'],
        'deskripsi'       => $data_resep['deskripsi'],
        'tanggal'         => $data_resep['tanggal'], // "2025-11-25"
        
        'equipment'       => $list_alat,

        'jumlah_kopi'     => intval($data_resep['jumlah_kopi']),
        'jumlah_air'      => intval($data_resep['jumlah_air']),
        'grind_size'      => $data_resep['ukuran_gilingan'],
        'suhu'            => intval($data_resep['suhu']),
        'brew_weight'     => intval($data_resep['berat_minuman']),
        'tds'             => intval($data_resep['tds']),
        'waktu_ekstraksi' => intval($data_resep['waktu_ekstraksi']),
        
        'ratio_text'      => "1:" . $ratio,
        
        'metode'          => $data_resep['nama_metode'],
        'pembuat'         => $data_resep['nama_pembuat']
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Detail resep berhasil diambil',
        'data'    => $response_data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
