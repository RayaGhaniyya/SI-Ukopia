<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

// 1. Cek Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// 2. Ambil ID Resep
$id_resep = intval($_POST['id_resep'] ?? 0);
if ($id_resep <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID Resep tidak valid!']));
}

// 3. Ambil Data Input
$uid_akun    = intval($_POST['uid_akun'] ?? 0);
$id_metode   = intval($_POST['id_metode'] ?? 0); // [PENTING]
$nama_resep  = trim($_POST['nama_resep'] ?? '');
$jumlah_kopi = intval($_POST['jumlah_kopi'] ?? 0);
$jumlah_air  = intval($_POST['jumlah_air'] ?? 0);
$suhu        = intval($_POST['suhu'] ?? 0);
$gilingan    = trim($_POST['ukuran_gilingan'] ?? '');
$waktu       = intval($_POST['waktu_ekstraksi'] ?? 0);
$berat       = intval($_POST['berat_minuman'] ?? 0);
$tds         = intval($_POST['tds'] ?? 0);
$deskripsi   = trim($_POST['deskripsi'] ?? '');

// Array Alat
$alat_selected = isset($_POST['alat']) ? $_POST['alat'] : [];

// Validasi
if ($uid_akun <= 0 || $id_metode <= 0 || empty($nama_resep)) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak lengkap (Nama, User, Metode)!']));
}

// 4. Mulai Transaksi
$conn->begin_transaction();

try {
    // A. Update Data Utama
    $query = "UPDATE resep SET 
                uid_akun=?, id_metode=?, nama_resep=?, ukuran_gilingan=?, 
                jumlah_air=?, suhu=?, jumlah_kopi=?, deskripsi=?, 
                waktu_ekstraksi=?, berat_minuman=?, tds=? 
              WHERE id_resep=?";
    
    $stmt = $conn->prepare($query);
    
    // Urutan Binding (12 parameter):
    // i(uid), i(metode), s(nama), s(giling), i(air), i(suhu), i(kopi), s(deskripsi), i(waktu), i(berat), i(tds), i(id_resep)
    $stmt->bind_param("iisssiiisiii", 
        $uid_akun, $id_metode, $nama_resep, $gilingan, 
        $jumlah_air, $suhu, $jumlah_kopi, $deskripsi, 
        $waktu, $berat, $tds, $id_resep
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data resep: " . $stmt->error);
    }
    $stmt->close();

    // B. Reset Detail Alat (Hapus semua lalu insert ulang)
    $conn->query("DELETE FROM resep_detail_alat WHERE id_resep = $id_resep");

    // C. Insert Ulang Alat
    if (!empty($alat_selected)) {
        $stmt_detail = $conn->prepare("INSERT INTO resep_detail_alat (id_resep, id_alat) VALUES (?, ?)");
        foreach ($alat_selected as $ida) {
            $ida = intval($ida);
            $stmt_detail->bind_param("ii", $id_resep, $ida);
            if (!$stmt_detail->execute()) throw new Exception("Gagal simpan alat.");
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