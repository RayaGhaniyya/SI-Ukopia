<?php
// Sesuaikan path koneksi
include("../../../Koneksi/koneksi.php");

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 1. Cek Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. Ambil Data JSON dari Body
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// 3. Validasi Data Wajib (Tambahkan id_metode)
if (
    empty($data['uid_akun']) || 
    empty($data['id_metode']) || 
    empty($data['nama_resep']) || 
    empty($data['jumlah_kopi']) || 
    empty($data['jumlah_air'])
) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap (UID, Metode, Nama, Kopi, Air wajib diisi)!']);
    exit;
}

// Ambil variabel
$uid_akun        = intval($data['uid_akun']);
$id_metode       = intval($data['id_metode']); // [BARU] Sesuai DB
$nama_resep      = trim($data['nama_resep']);
$ukuran_gilingan = $data['ukuran_gilingan'] ?? '-';
$jumlah_air      = intval($data['jumlah_air']); // Pastikan INT
$suhu            = intval($data['suhu'] ?? 90);
$jumlah_kopi     = intval($data['jumlah_kopi']); // Pastikan INT
$deskripsi       = $data['deskripsi'] ?? '';
$waktu_ekstraksi = intval($data['waktu_ekstraksi'] ?? 0);
$berat_minuman   = intval($data['berat_minuman'] ?? 0);
$tds             = intval($data['tds'] ?? 0); // [UBAH] DB pakai INT, bukan FLOAT
$tanggal         = date('Y-m-d');

// Array ID Alat
$list_alat = isset($data['alat']) && is_array($data['alat']) ? $data['alat'] : [];

// 4. Mulai Transaksi Database
$conn->begin_transaction();

try {
    // A. Insert Data Resep Utama (Tambahkan kolom id_metode)
    $query_resep = "INSERT INTO resep (uid_akun, nama_resep, ukuran_gilingan, jumlah_air, suhu, jumlah_kopi, deskripsi, waktu_ekstraksi, berat_minuman, tds, tanggal, id_metode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query_resep);
    
    // Bind Param: Tambahkan 'i' di akhir untuk id_metode
    // Urutan: uid(i), nama(s), gilingan(s), air(i), suhu(i), kopi(i), desk(s), waktu(i), berat(i), tds(i), tgl(s), metode(i)
    $stmt->bind_param("issiiisiidsi", 
        $uid_akun, 
        $nama_resep, 
        $ukuran_gilingan, 
        $jumlah_air, 
        $suhu, 
        $jumlah_kopi, 
        $deskripsi, 
        $waktu_ekstraksi, 
        $berat_minuman, 
        $tds, 
        $tanggal, 
        $id_metode 
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data resep: " . $stmt->error);
    }
    
    $id_resep_baru = $conn->insert_id;
    $stmt->close();

    // B. Insert Detail Alat (Tetap sama, tidak berubah)
    if (!empty($list_alat)) {
        $query_alat = "INSERT INTO resep_detail_alat (id_resep, id_alat) VALUES (?, ?)";
        $stmt_alat = $conn->prepare($query_alat);

        foreach ($list_alat as $id_alat) {
            $id_alat_int = intval($id_alat);
            $stmt_alat->bind_param("ii", $id_resep_baru, $id_alat_int);
            
            if (!$stmt_alat->execute()) {
                throw new Exception("Gagal menyimpan detail alat ID: " . $id_alat);
            }
        }
        $stmt_alat->close();
    }

    // C. Commit Transaksi
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Resep berhasil disimpan!',
        'id_resep' => $id_resep_baru
    ]);

} catch (Exception $e) {
    // D. Rollback jika ada error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>