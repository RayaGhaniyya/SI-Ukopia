<?php
include("../../../Koneksi/koneksi.php"); 


$id_kategori = isset($_GET['id_kategori']) ? intval($_GET['id_kategori']) : 0;

header('Content-Type: application/json');

if ($id_kategori > 0) {
    $stmt = $conn->prepare("SELECT id_menu, nama_menu FROM menu WHERE id_kategori = ? ORDER BY nama_menu ASC");
    $stmt->bind_param("i", $id_kategori);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $menus = [];
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
        echo json_encode($menus);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
