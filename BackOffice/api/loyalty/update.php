<?php
include("../../../Koneksi/koneksi.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Ambil JSON Input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validasi ID
if (empty($data['id_loyalty']) || empty($data['uid_akun'])) {
    exit(json_encode(['success' => false, 'message' => 'ID Loyalty & UID wajib diisi']));
}

$id_loyalty = intval($data['id_loyalty']);
$uid_akun   = intval($data['uid_akun']);

// Ambil Nilai (Default 0 jika tidak diisi)
$keasaman   = intval($data['keasaman'] ?? 0);
$kepahitan  = intval($data['kepahitan'] ?? 0);
$aroma      = intval($data['aroma'] ?? 0);
$kemanisan  = intval($data['kemanisan'] ?? 0);
$kekentalan = intval($data['kekentalan'] ?? 0);
$catatan    = trim($data['catatan'] ?? '');

try {
    // Cek apakah data ini benar milik user tersebut
    $check = $conn->prepare("SELECT id_loyalty FROM loyalty WHERE id_loyalty = ? AND uid_akun = ?");
    $check->bind_param("ii", $id_loyalty, $uid_akun);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        throw new Exception("Data tidak ditemukan atau bukan milik Anda.");
    }

    // Update Data & Ubah Status jadi 'Selesai'
    $sql = "UPDATE loyalty SET 
            keasaman = ?, kepahitan = ?, aroma = ?, kemanisan = ?, kekentalan = ?, 
            catatan = ?, status_pengisian = 'Selesai'
            WHERE id_loyalty = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiisi", $keasaman, $kepahitan, $aroma, $kemanisan, $kekentalan, $catatan, $id_loyalty);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Review berhasil disimpan! Poin Anda bertambah +1.'
        ]);
    } else {
        throw new Exception("Gagal menyimpan review.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>