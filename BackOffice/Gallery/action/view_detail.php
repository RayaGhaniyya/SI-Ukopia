<?php
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID galeri tidak valid!']);
    exit;
}

try {
    
    $stmt_check = $conn->prepare("SELECT judul FROM galery WHERE id_galery = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Data galeri tidak ditemukan!']);
        exit;
    }

    $galery_data = $result_check->fetch_assoc();
    $stmt_check->close();

    
    $stmt = $conn->prepare("SELECT gambar FROM detail_galery WHERE id_galery = ? ORDER BY id_detail_galery ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $images = [];
    while ($row = $result->fetch_assoc()) {
        
        $images[] = "../" . $row['gambar'];
    }

    $stmt->close();

    if (empty($images)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada gambar untuk galeri ini'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'images' => $images,
        'total' => count($images),
        'title' => $galery_data['judul']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

$conn->close();
