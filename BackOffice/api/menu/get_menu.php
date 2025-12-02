<?php
header('Content-Type: application/json');
include_once '../../config/database.php'; // Ini HARUSNYA sudah berisi fungsi di bawah

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']);
    die();
}

/**
 * Membangun URL gambar yang lengkap dari nama file di database.
 * Database sekarang hanya menyimpan "nama_file.webp".
 * Fungsi ini mengubahnya menjadi "http://[host_anda]/[path_upload]/nama_file.webp"
 */
function perbaiki_url_gambar($nama_file_dari_db) {
    if (empty($nama_file_dari_db)) {
        return null; // Kembalikan null jika tidak ada gambar
    }

    $current_host = $_SERVER['HTTP_HOST'];

    $base_path = "/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/";

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $correct_url = $protocol . $current_host . $base_path . $nama_file_dari_db;

    return $correct_url;
}


$id_kategori = isset($_GET['id_kategori']) ? intval($_GET['id_kategori']) : 0;

$where_clause = "";
if ($id_kategori > 0) {
    $where_clause = "WHERE m.id_kategori = ?";
}

$response = []; 
$menu_list = []; 

$sql = "SELECT 
            m.id_menu, 
            km.nama_kategori,
            m.deskripsi, 
            m.gambar_url, -- Ini sekarang berisi 
            m.nama_menu,
            TRUNCATE(AVG(um.rating), 1) AS average_rating,
            COUNT(um.id_menu) AS total_reviews
        FROM 
            menu m
        JOIN 
            kategori_menu km ON m.id_kategori = km.id_kategori_menu
        LEFT JOIN 
            ulasan_menu um ON m.id_menu = um.id_menu
        $where_clause
        GROUP BY 
            m.id_menu, km.nama_kategori, m.deskripsi, m.gambar_url, m.nama_menu";

$stmt = $db->prepare($sql);

if ($id_kategori > 0) {
    $stmt->bind_param("i", $id_kategori);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $row['gambar_url'] = perbaiki_url_gambar($row['gambar_url']);
        
        if (is_null($row['average_rating'])) {
            $row['average_rating'] = 0;
        }

        $menu_list[] = $row;
    }
    
    $response['status'] = 'success';
    $response['data'] = $menu_list;
    
} else {
    $response['status'] = 'success';
    $response['message'] = 'Data menu tidak ditemukan';
    $response['data'] = []; 
}

echo json_encode($response, JSON_NUMERIC_CHECK);
$stmt->close();
$db->close();
?>
