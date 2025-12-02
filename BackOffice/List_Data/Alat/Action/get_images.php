<?php
include("../../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
$kategori_id = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;
if ($kategori_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kategori ID tidak valid']);
    exit;
}
try {
    $query = "
        SELECT 
            gambar as filename,
            COUNT(*) as usage_count,
            MIN(nama_alat) as sample_alat
        FROM alat 
        WHERE id_kategori_alat = ? AND gambar != '' 
        GROUP BY gambar
        ORDER BY usage_count DESC, sample_alat ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $kategori_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = [
            'filename' => $row['filename'],
            'usage_count' => $row['usage_count'],
            'sample_alat' => $row['sample_alat']
        ];
    }
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'images' => $images
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

