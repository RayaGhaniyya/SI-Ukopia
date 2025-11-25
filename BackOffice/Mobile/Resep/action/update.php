<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method invalid']));
}

$id_resep = intval($_POST['id_resep'] ?? 0);
if ($id_resep <= 0) exit(json_encode(['success' => false, 'message' => 'ID Invalid']));

// Ambil Data Post
$uid_akun = intval($_POST['uid_akun']);
$nama_resep = $_POST['nama_resep'];
// ... Ambil sisa variabel sama seperti store.php ...
$jumlah_kopi = $_POST['jumlah_kopi'];
$jumlah_air = $_POST['jumlah_air'];
$suhu = $_POST['suhu'];
$gilingan = $_POST['ukuran_gilingan'];
$waktu = $_POST['waktu_ekstraksi'];
$berat = $_POST['berat_minuman'];
$tds = $_POST['tds'];
$deskripsi = $_POST['deskripsi'];

$alat_selected = isset($_POST['alat']) ? $_POST['alat'] : [];

$conn->begin_transaction();

try {
    // 1. Update Data Utama
    $stmt = $conn->prepare("UPDATE resep SET uid_akun=?, nama_resep=?, ukuran_gilingan=?, jumlah_air=?, suhu=?, jumlah_kopi=?, deskripsi=?, waktu_ekstraksi=?, berat_minuman=?, tds=? WHERE id_resep=?");
    $stmt->bind_param("issssssiidi", $uid_akun, $nama_resep, $gilingan, $jumlah_air, $suhu, $jumlah_kopi, $deskripsi, $waktu, $berat, $tds, $id_resep);
    
    if (!$stmt->execute()) throw new Exception("Gagal update data resep.");
    $stmt->close();

    // 2. HAPUS Semua Detail Alat Lama (Reset)
    $conn->query("DELETE FROM resep_detail_alat WHERE id_resep = $id_resep");

    // 3. Masukkan Detail Alat Baru
    if (!empty($alat_selected)) {
        $stmt_detail = $conn->prepare("INSERT INTO resep_detail_alat (id_resep, id_alat) VALUES (?, ?)");
        foreach ($alat_selected as $id_alat) {
            $id_alat = intval($id_alat);
            $stmt_detail->bind_param("ii", $id_resep, $id_alat);
            $stmt_detail->execute();
        }
        $stmt_detail->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Resep berhasil diperbarui!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>