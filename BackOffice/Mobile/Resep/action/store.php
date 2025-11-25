<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

// Ambil Data
$uid_akun = intval($_POST['uid_akun'] ?? 0);
$nama_resep = trim($_POST['nama_resep'] ?? '');
$jumlah_kopi = trim($_POST['jumlah_kopi'] ?? '');
$jumlah_air = trim($_POST['jumlah_air'] ?? '');
$suhu = trim($_POST['suhu'] ?? '');
$gilingan = trim($_POST['ukuran_gilingan'] ?? '');
$waktu = intval($_POST['waktu_ekstraksi'] ?? 0);
$berat = intval($_POST['berat_minuman'] ?? 0);
$tds = floatval($_POST['tds'] ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$tanggal = date('Y-m-d');

$alat_selected = isset($_POST['alat']) ? $_POST['alat'] : []; // Array ID Alat

if ($uid_akun <= 0 || empty($nama_resep)) {
    exit(json_encode(['success' => false, 'message' => 'Data wajib (Nama/User) tidak boleh kosong!']));
}

// MULAI TRANSAKSI
$conn->begin_transaction();

try {
    // 1. Insert Table Resep
    $stmt = $conn->prepare("INSERT INTO resep (uid_akun, nama_resep, ukuran_gilingan, jumlah_air, suhu, jumlah_kopi, deskripsi, waktu_ekstraksi, berat_minuman, tds, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssiids", $uid_akun, $nama_resep, $gilingan, $jumlah_air, $suhu, $jumlah_kopi, $deskripsi, $waktu, $berat, $tds, $tanggal);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data utama Resep.");
    }
    $id_resep = $conn->insert_id;
    $stmt->close();

    // 2. Insert Detail Alat (Looping)
    if (!empty($alat_selected)) {
        $stmt_detail = $conn->prepare("INSERT INTO resep_detail_alat (id_resep, id_alat) VALUES (?, ?)");
        foreach ($alat_selected as $id_alat) {
            $id_alat = intval($id_alat);
            $stmt_detail->bind_param("ii", $id_resep, $id_alat);
            if (!$stmt_detail->execute()) {
                throw new Exception("Gagal menyimpan detail alat.");
            }
        }
        $stmt_detail->close();
    }

    // COMMIT jika semua lancar
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Resep berhasil dibuat!']);

} catch (Exception $e) {
    // ROLLBACK jika ada error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>