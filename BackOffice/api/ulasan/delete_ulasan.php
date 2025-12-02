<?php
header('Content-Type: application/json');
include_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));
if (
    !isset($data->id_ulasan) ||
    !isset($data->uid_akun) // ID user yang sedang login (untuk keamanan)
) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit();
}
$sql = "DELETE FROM ulasan_menu WHERE id_ulasan = ? AND uid_akun = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("ii", $data->id_ulasan, $data->uid_akun);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Ulasan berhasil dihapus.']);
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus ulasan. Pastikan ulasan ini milik Anda.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $stmt->error]);
}
$stmt->close();
$db->close();
?>

