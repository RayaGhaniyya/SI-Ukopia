<?php
header('Content-Type: application/json');
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->id_menu) ||
    !isset($data->uid_akun) ||
    !isset($data->rating) ||
    !isset($data->komentar)
) {
    http_response_code(400); 
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit();
}

// 3. Siapkan data
$id_menu = $data->id_menu;
$uid_akun = $data->uid_akun;
$rating = $data->rating;
$komentar = $data->komentar;
$tanggal_waktu = date('Y-m-d H:i:s'); // Tanggal & waktu saat ini

// 4. Kueri INSERT ... ON DUPLICATE KEY UPDATE
// Ini adalah kueri "pintar"
// Dia akan mencoba INSERT. Jika gagal karena 'uid_id_menu_unique'
// (yang kita buat di Langkah 1), dia akan menjalankan bagian UPDATE.
$sql = "INSERT INTO ulasan_menu 
            (id_menu, uid_akun, rating, tanggal_waktu, komentar) 
        VALUES 
            (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            rating = ?, 
            komentar = ?,
            tanggal_waktu = ?";

$stmt = $db->prepare($sql);

// i, i, d, s, s,  (untuk bagian INSERT)
// d, s, s          (untuk bagian UPDATE)
$stmt->bind_param("iidssdss", 
    $id_menu, $uid_akun, $rating, $tanggal_waktu, $komentar, // Bagian INSERT
    $rating, $komentar, $tanggal_waktu                      // Bagian UPDATE
);

// 5. Eksekusi dan kirim respons
if ($stmt->execute()) {
    // $stmt->affected_rows akan:
    // 1 = Jika ulasan baru berhasil di-INSERT
    // 2 = Jika ulasan lama berhasil di-UPDATE (dengan data yang berbeda)
    // 0 = Jika ulasan lama di-UPDATE (tapi datanya sama persis)
    echo json_encode(['status' => 'success', 'message' => 'Ulasan berhasil disimpan.']);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => 'Ulasan gagal disimpan: ' . $stmt->error]);
}

$stmt->close();
$db->close();
?>