<?php
// File: FrontOffice/Reservation/action/store.php

header('Content-Type: application/json');
include("../../../Koneksi/koneksi.php"); // Sesuaikan path koneksi

// PENTING: Pastikan variabel koneksi kamu adalah $conn
if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

// Tangkap data yang dikirim oleh JavaScript (fetch)
$nama_pelanggan = $_POST['nama_pelanggan'] ?? null;
$no_telepon = $_POST['no_telepon'] ?? null;
$tanggal = $_POST['tanggal'] ?? null;
$jam = $_POST['jam'] ?? null;
$status = 'Pending'; // Status default saat customer pesan

// Validasi sederhana
if (empty($nama_pelanggan) || empty($no_telepon) || empty($tanggal) || empty($jam)) {
    echo json_encode([
        'success' => false,
        'message' => 'Semua data wajib diisi.'
    ]);
    exit;
}

// Cek apakah slot sudah di-book (double check)
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


// Gunakan prepared statements untuk keamanan (ANTI SQL INJECTION)
// PENTING: Pastikan nama kolom di sini (nama_pelanggan, no_telepon, tanggal, jam, status)
// Sesuai 100% dengan database kamu
$stmt = $conn->prepare(
    "INSERT INTO reservasi (nama_pelanggan, no_telepon, tanggal, jam, status) 
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param("sssss", $nama_pelanggan, $no_telepon, $tanggal, $jam, $status);

if ($stmt->execute()) {
    // Jika berhasil
    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil dibuat! Silakan tunggu konfirmasi admin.'
    ]);
} else {
    // Jika gagal
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan reservasi: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
