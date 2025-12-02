<?php

header('Content-Type: application/json');
include("../../../Koneksi/koneksi.php"); // Sesuaikan path koneksi

if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

$nama_pelanggan = $_POST['nama_pelanggan'] ?? null;
$no_telepon = $_POST['no_telepon'] ?? null;
$tanggal = $_POST['tanggal'] ?? null;
$jam = $_POST['jam'] ?? null;
$status = 'Pending'; // Status default saat customer pesan

if (empty($nama_pelanggan) || empty($no_telepon) || empty($tanggal) || empty($jam)) {
    echo json_encode([
        'success' => false,
        'message' => 'Semua data wajib diisi.'
    ]);
    exit;
}

$stmt_check = $conn->prepare("SELECT id_reservasi FROM reservasi WHERE tanggal = ? AND jam = ? AND status = 'Confirmed'");
$stmt_check->bind_param("ss", $tanggal, $jam);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Maaf, slot waktu ini sudah dipesan. Silakan pilih jam lain.'
    ]);
    $stmt_check->close();
    exit;
}
$stmt_check->close();


$stmt = $conn->prepare(
    "INSERT INTO reservasi (nama_pelanggan, no_telepon, tanggal, jam, status) 
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param("sssss", $nama_pelanggan, $no_telepon, $tanggal, $jam, $status);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil dibuat! Silakan tunggu konfirmasi admin.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan reservasi: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

