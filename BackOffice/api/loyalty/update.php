<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_loyalty) || empty($data->uid_akun) || !isset($data->catatan)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data ID Loyalty, UID Akun, atau Catatan tidak lengkap.']);
    exit();
}

try {
    $id_loyalty = intval($data->id_loyalty);
    $uid_akun = intval($data->uid_akun);
    $catatan = trim($data->catatan);

    $keasaman = isset($data->keasaman) ? intval($data->keasaman) : 0;
    $kepahitan = isset($data->kepahitan) ? intval($data->kepahitan) : 0;
    $aroma = isset($data->aroma) ? intval($data->aroma) : 0;
    $kemanisan = isset($data->kemanisan) ? intval($data->kemanisan) : 0;
    $kekentalan = isset($data->kekentalan) ? intval($data->kekentalan) : 0;

    $check_sql = "SELECT status_pengisian FROM loyalty WHERE id_loyalty = ? AND uid_akun = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id_loyalty, $uid_akun);
    $check_stmt->execute();
    $old_status = $check_stmt->get_result()->fetch_assoc()['status_pengisian'] ?? null;
    $check_stmt->close();
    
    if ($old_status === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data loyalty tidak ditemukan atau bukan milik user ini.']);
        exit();
    }
    
    $is_first_review = ($old_status === 'Menunggu Review');
    
    $update_sql = "UPDATE loyalty SET 
                    catatan = ?, 
                    aroma = ?, 
                    kemanisan = ?, 
                    keasaman = ?, 
                    kepahitan = ?, 
                    kekentalan = ?";
    
    if ($is_first_review) {
        $update_sql .= ", status_pengisian = 'Selesai'"; // Pemicu trigger poin
    }
    
    $update_sql .= " WHERE id_loyalty = ? AND uid_akun = ?";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("siiiiiii", 
        $catatan, 
        $aroma, 
        $kemanisan, 
        $keasaman, 
        $kepahitan, 
        $kekentalan,
        $id_loyalty, 
        $uid_akun
    );
    
    $update_stmt->execute();
    $rows_affected = $update_stmt->affected_rows;
    $update_stmt->close();
    
    if ($rows_affected == 0 && $old_status === 'Menunggu Review') {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan review. Pastikan ID dan UID benar.']);
        exit();
    }
    
    $point_sql = "SELECT total_poin FROM akun_customer WHERE uid = ?";
    $point_stmt = $conn->prepare($point_sql);
    $point_stmt->bind_param("i", $uid_akun);
    $point_stmt->execute();
    $total_points = $point_stmt->get_result()->fetch_assoc()['total_poin'] ?? 0;
    $point_stmt->close();

    function getClaimDate($conn, $uid, $reward_id) {
        $sql = "SELECT tanggal_dapat FROM riwayat_reward WHERE uid_customer = ? AND id_reward = ? AND status_klaim = 'Sudah Dipakai' ORDER BY tanggal_dapat DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $uid, $reward_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['tanggal_dapat'] ?? null;
    }
    
    $status_data = [
        "total_points" => $total_points,
        "discount10_claim_date" => getClaimDate($conn, $uid_akun, 1), // 5 pts (ID 1)
        "free_serve_claim_date" => getClaimDate($conn, $uid_akun, 2), // 10 pts (ID 2)
        "free_tshirt_claim_date" => getClaimDate($conn, $uid_akun, 3), // 20 pts (ID 3)
        "discount10_slot15_claim_date" => null, 
        "discount10_25_claim_date" => null,
        "free_serve_30_claim_date" => null,
        "discount10_35_claim_date" => null,
        "free_serve_40_claim_date" => null,
        "discount10_45_claim_date" => null,
        "free_serve_50_claim_date" => null,
        "discount10_55_claim_date" => null,
        "free_serve_60_claim_date" => null,
        "discount10_65_claim_date" => null,
        "free_serve_70_claim_date" => null,
        "discount10_75_claim_date" => null,
        "free_serve_80_claim_date" => null,
        "discount10_85_claim_date" => null,
        "free_serve_90_claim_date" => null,
        "discount10_95_claim_date" => null
    ];
    
    $response = [
        "success" => true,
        "message" => "Review berhasil disimpan. Poin diperbarui.",
        "data" => $status_data // Mengembalikan status reward lengkap
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
