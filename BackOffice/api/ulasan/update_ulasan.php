<?php
header('Content-Type: application/json');
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->id_ulasan) ||
    !isset($data->uid_akun) || // ID user yang sedang login (untuk keamanan)
    !isset($data->rating) ||
    !isset($data->komentar)
) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit();
}

// Keamanan: Update HANYA jika id_ulasan dan uid_akun cocok
$sql = "UPDATE ulasan_menu SET 
            rating = ?, 
            komentar = ?,
            tanggal_waktu = NOW() 
        WHERE 
            id_ulasan = ? AND uid_akun = ?"; // Cek keamanan

$stmt = $db->prepare($sql);
// 'dsii' -> double, string, integer, integer
$stmt->bind_param("dsii", $data->rating, $data->komentar, $data->id_ulasan, $data->uid_akun);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Ulasan berhasil diperbarui.']);
    } else {
        // Ini terjadi jika ID ulasan salah atau user mencoba mengedit ulasan orang lain
        http_response_code(403); // Forbidden
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui ulasan. Pastikan ulasan ini milik Anda.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $stmt->error]);
}

$stmt->close();
$db->close();
?>