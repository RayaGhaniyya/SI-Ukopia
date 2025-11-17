<?php
session_start();
// [PATH] Sesuaikan path ke file koneksi Anda
include("../../../Koneksi/koneksi.php");

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Terjadi kesalahan saat menyimpan alamat.'
];

// 1. Validasi Sesi Login
if (!isset($_SESSION['customer_uid'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit;
}
$customer_uid = $_SESSION['customer_uid'];

// 2. Ambil semua data dari POST
// (Kita asumsikan JS sudah memvalidasi 'required', 
//  tapi idealnya PHP juga validasi ulang)
$label_alamat = $_POST['label_alamat'] ?? '';
$nama_penerima = $_POST['nama_penerima'] ?? '';
$no_telepon = $_POST['no_telepon'] ?? '';
$kode_pos = $_POST['kode_pos'] ?? '';
$kota = $_POST['kota'] ?? ''; // Anda sudah perbaiki ini di HTML
$provinsi = $_POST['provinsi'] ?? '';
$alamat_lengkap = $_POST['alamat_lengkap'] ?? '';

// Cek 'is_utama'. Jika checkbox dicentang, nilainya "1", jika tidak, 'isset' akan false.
$is_utama = isset($_POST['is_utama']) ? 1 : 0;

// Validasi sederhana di sisi server
if (empty($label_alamat) || empty($nama_penerima) || empty($no_telepon) || empty($alamat_lengkap)) {
    $response['message'] = 'Semua field wajib diisi.';
    echo json_encode($response);
    exit;
}

// 3. Gunakan Transaksi Database
// Ini penting untuk memastikan konsistensi data
$conn->begin_transaction();

try {
    // 3a. JIKA ini adalah alamat utama, set semua alamat lain milik user ini ke is_utama = 0
    if ($is_utama == 1) {
        $sql_clear_utama = "UPDATE alamat_customer SET is_utama = 0 WHERE uid_customer = ?";
        $stmt_clear = $conn->prepare($sql_clear_utama);
        $stmt_clear->bind_param("i", $customer_uid);
        $stmt_clear->execute();
        $stmt_clear->close();
    }

    // 3b. Masukkan alamat baru
    $sql_insert = "INSERT INTO alamat_customer 
                   (uid_customer, label_alamat, nama_penerima, no_telepon, alamat_lengkap, kota, provinsi, kode_pos, is_utama) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param(
        "isssssssi", // i = integer, s = string
        $customer_uid,
        $label_alamat,
        $nama_penerima,
        $no_telepon,
        $alamat_lengkap,
        $kota,
        $provinsi,
        $kode_pos,
        $is_utama
    );

    if ($stmt_insert->execute()) {
        // Jika semua berhasil
        $conn->commit(); // Simpan semua perubahan
        $response['success'] = true;
        $response['message'] = 'Alamat baru berhasil disimpan!';
    } else {
        // Jika insert gagal
        throw new Exception('Gagal mengeksekusi statement insert.');
    }

    $stmt_insert->close();
} catch (Exception $e) {
    // Jika terjadi error di salah satu langkah, batalkan semua
    $conn->rollback();
    $response['message'] = 'Error: ' . $e->getMessage();
}

$conn->close();

// 4. Kembalikan respons
echo json_encode($response);
