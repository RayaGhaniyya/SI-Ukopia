<?php
include("../../../Koneksi/koneksi.php");

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (empty($data['id_resep'])) {
    echo json_encode(['success' => false, 'message' => 'ID Resep wajib diisi!']);
    exit;
}

$id_resep = intval($data['id_resep']);
$uid_akun = isset($data['uid_akun']) ? intval($data['uid_akun']) : 0;

if ($uid_akun > 0) {
    $cek_milik = $conn->prepare("SELECT id_resep FROM resep WHERE id_resep = ? AND uid_akun = ?");
    $cek_milik->bind_param("ii", $id_resep, $uid_akun);
    $cek_milik->execute();
    $result = $cek_milik->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Resep tidak ditemukan atau bukan milik Anda!']);
        exit;
    }
    $cek_milik->close();
}

try {
    $query = "DELETE FROM resep WHERE id_resep = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_resep);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Resep berhasil dihapus!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resep tidak ditemukan atau sudah terhapus.']);
        }
    } else {
        throw new Exception("Gagal menghapus data.");
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>