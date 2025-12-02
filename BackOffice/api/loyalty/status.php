<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uid_akun = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid_akun <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'UID wajib diisi.']);
    exit();
}

try {
    // Fungsi untuk mendapatkan tanggal klaim reward
    function getClaimDate($conn, $uid, $reward_id) {
        $sql = "SELECT tanggal_dapat FROM riwayat_reward WHERE uid_customer = ? AND id_reward = ? AND status_klaim = 'Sudah Dipakai' ORDER BY tanggal_dapat DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $uid, $reward_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['tanggal_dapat'] ?? null;
    }

    // 1. Ambil total poin terbaru dari akun_customer
    $point_sql = "SELECT total_poin FROM akun_customer WHERE uid = ?";
    $point_stmt = $conn->prepare($point_sql);
    $point_stmt->bind_param("i", $uid_akun);
    $point_stmt->execute();
    $total_points = $point_stmt->get_result()->fetch_assoc()['total_poin'] ?? 0;
    $point_stmt->close();
    
    // 2. Mapping Reward ID dari DB ke field di LoyaltyUserStatus.kt
    $status_data = [
        "total_points" => $total_points,
        "discount10_claim_date" => getClaimDate($conn, $uid_akun, 1), // 5 pts (ID 1)
        "free_serve_claim_date" => getClaimDate($conn, $uid_akun, 2), // 10 pts (ID 2)
        "free_tshirt_claim_date" => getClaimDate($conn, $uid_akun, 3), // 20 pts (ID 3)
        // Sisanya diisi null untuk menyesuaikan struktur LoyaltyUserStatus.kt
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
    
    // 3. Bentuk Response
    $response = [
        "success" => true,
        "message" => "Status loyalty berhasil diambil.",
        "data" => $status_data
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>