<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}


$uid_akun = intval($_POST['uid_akun'] ?? 0);
$id_metode = intval($_POST['id_metode'] ?? 0);
$nama_resep = trim($_POST['nama_resep'] ?? '');


$jumlah_kopi = intval($_POST['jumlah_kopi'] ?? 0);
$jumlah_air = intval($_POST['jumlah_air'] ?? 0);
$suhu = intval($_POST['suhu'] ?? 0);
$gilingan = trim($_POST['ukuran_gilingan'] ?? '');
$waktu = intval($_POST['waktu_ekstraksi'] ?? 0);
$berat = intval($_POST['berat_minuman'] ?? 0);
$tds = intval($_POST['tds'] ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$tanggal = date('Y-m-d');

$alat_selected = isset($_POST['alat']) ? $_POST['alat'] : [];


if ($uid_akun <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Pemilik Resep wajib dipilih!']));
}
if ($id_metode <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Metode Seduh wajib dipilih!']));
}
if (empty($nama_resep)) {
    exit(json_encode(['success' => false, 'message' => 'Nama Resep wajib diisi!']));
}


$conn->begin_transaction();

try {

    $stmt = $conn->prepare("INSERT INTO resep (uid_akun, nama_resep, ukuran_gilingan, jumlah_air, suhu, jumlah_kopi, deskripsi, waktu_ekstraksi, berat_minuman, tds, tanggal, id_metode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");



    $stmt->bind_param(
        "issiiisiidsi",
        $uid_akun,
        $nama_resep,
        $gilingan,
        $jumlah_air,
        $suhu,
        $jumlah_kopi,
        $deskripsi,
        $waktu,
        $berat,
        $tds,
        $tanggal,
        $id_metode
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data resep: " . $stmt->error);
    }
    $id_resep = $conn->insert_id;
    $stmt->close();


    if (!empty($alat_selected)) {
        $stmt_detail = $conn->prepare("INSERT INTO resep_detail_alat (id_resep, id_alat) VALUES (?, ?)");
        foreach ($alat_selected as $ida) {
            $ida = intval($ida);
            $stmt_detail->bind_param("ii", $id_resep, $ida);
            if (!$stmt_detail->execute()) {
                throw new Exception("Gagal menyimpan alat.");
            }
        }
        $stmt_detail->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Resep berhasil dibuat!']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
