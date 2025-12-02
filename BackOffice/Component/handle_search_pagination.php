<?php

if (!isset($conn) || !isset($table_name) || !isset($base_order_by)) {
    die("Kesalahan Developer: Helper pagination.php tidak mendapatkan variabel yang dibutuhkan.");
}


$limit = 20; // Data per halaman
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? ''; 

$base_url_pagin = '?'; 
$where_conditions = []; 
$params = []; 
$types = ""; 

if ($search_term != '') {
    $search_like = "%" . $search_term . "%";
    $where_conditions[] = "(nama_pelanggan LIKE ? OR no_telepon LIKE ? OR tanggal LIKE ?)";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "sss";
    $base_url_pagin .= 'search=' . urlencode($search_term) . '&';
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}

$count_query = "SELECT COUNT(*) as total FROM " . $table_name . $where_sql;

$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();


$order_by_sql = $base_order_by . " LIMIT ? OFFSET ?"; 

$data_query = "SELECT * FROM " . $table_name . $where_sql . $order_by_sql;

$data_params = $params;
$data_params[] = $limit;
$data_params[] = $offset;
$data_types = $types . "ii"; 

$stmt_data = $conn->prepare($data_query);
$stmt_data->bind_param($data_types, ...$data_params);
$stmt_data->execute();
$result = $stmt_data->get_result();

?>
