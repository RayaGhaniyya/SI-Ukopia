<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode([
        'success' => false, 
        'message' => 'Method tidak valid'
    ]));
}
try {
    $nama_kategori_alat = trim($_POST['nama_kategori_alat'] ?? '');
    if (empty($nama_kategori_alat)) {
        throw new Exception('Nama kategori wajib diisi!');
    }
    if (strlen($nama_kategori_alat) > 100) {
        throw new Exception('Nama kategori maksimal 100 karakter!');
    }
    $stmt_check = $conn->prepare("SELECT id_kategori_alat FROM kategori_alat WHERE LOWER(nama_kategori_alat) = LOWER(?)");
    $stmt_check->bind_param("s", $nama_kategori_alat);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        throw new Exception('Kategori dengan nama tersebut sudah ada!');
    }
    $stmt_check->close();
    $stmt = $conn->prepare("INSERT INTO kategori_alat (nama_kategori_alat) VALUES (?)");
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query: ' . $conn->error);
    }
    $stmt->bind_param("s", $nama_kategori_alat);
    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data: ' . $stmt->error);
    }
    $id_kategori_alat = $conn->insert_id;
    $stmt->close();
    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan!',
        'id_kategori_alat' => $id_kategori_alat
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>

