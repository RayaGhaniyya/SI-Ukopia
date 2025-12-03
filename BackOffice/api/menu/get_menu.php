<?php
header('Content-Type: application/json');
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']);
    die();
}

function perbaiki_url_gambar($nama_file_dari_db) {
    if (empty($nama_file_dari_db)) {
        return null; 
    }

    $current_host = $_SERVER['HTTP_HOST'];
    $base_path = "/SI-Ukopia/BackOffice/Mobile/Uploads/Menu/"; 
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $correct_url = $protocol . $current_host . $base_path . $nama_file_dari_db;

    return $correct_url;
}

$id_kategori = isset($_GET['id_kategori']) ? intval($_GET['id_kategori']) : 0;
$id_menu = isset($_GET['id_menu']) ? intval($_GET['id_menu']) : 0;

$where_clauses = [];
$bind_params_types = ""; 
$bind_params_values = []; 

if ($id_kategori > 0) {
    $where_clauses[] = "m.id_kategori = ?";
    $bind_params_types .= "i";
    $bind_params_values[] = $id_kategori;
}

if ($id_menu > 0) {
    $where_clauses[] = "m.id_menu = ?";
    $bind_params_types .= "i";
    $bind_params_values[] = $id_menu;
}

$where_clause_sql = "";
if (!empty($where_clauses)) {
    $where_clause_sql = "WHERE " . implode(" AND ", $where_clauses);
}

$response = []; 
$menu_list = []; 

$sql = "SELECT 
            m.id_menu, 
            km.nama_kategori,
            m.deskripsi, 
            m.gambar_url, 
            m.nama_menu,
            TRUNCATE(COALESCE(AVG(um.rating), 0), 1) AS average_rating,
            COUNT(um.id_menu) AS total_reviews
        FROM 
            menu m
        JOIN 
            kategori_menu km ON m.id_kategori = km.id_kategori_menu
        LEFT JOIN 
            ulasan_menu um ON m.id_menu = um.id_menu
        $where_clause_sql
        GROUP BY 
            m.id_menu, km.nama_kategori, m.deskripsi, m.gambar_url, m.nama_menu";

$stmt = $db->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare statement failed: ' . $db->error]);
    die();
}

if (!empty($bind_params_values)) {
    $params = array_merge([$bind_params_types], $bind_params_values);
    
    $refs = [];
    foreach($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['gambar_url'] = perbaiki_url_gambar($row['gambar_url']);
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