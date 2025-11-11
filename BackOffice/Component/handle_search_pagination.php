<?php
// File: BackOffice/Component/handle_search_pagination.php
//
// File ini adalah "mesin"-nya. Dia di-include oleh index.php atau riwayat.php
// Dia "mengharapkan" 3 variabel sudah ada:
// 1. $conn (dari koneksi.php)
// 2. $table_name (contoh: 'reservasi' atau 'reservasi_arsip')
// 3. $base_order_by (contoh: 'ORDER BY tanggal DESC')

if (!isset($conn) || !isset($table_name) || !isset($base_order_by)) {
    die("Kesalahan Developer: Helper pagination.php tidak mendapatkan variabel yang dibutuhkan.");
}

// --- LOGIKA HANYA UNTUK SEARCH & PAGINATION ---

$limit = 20; // Data per halaman
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $limit;

$search_term = $_GET['search'] ?? ''; 

$base_url_pagin = '?'; 
$where_conditions = []; 
$params = []; 
$types = ""; 

// --- Logika HANYA UNTUK Search ---
if ($search_term != '') {
    $search_like = "%" . $search_term . "%";
    // Kolom ini (nama_pelanggan, no_telepon, tanggal) kita asumsikan ada
    // di kedua tabel (reservasi & reservasi_arsip)
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

// 4. QUERY PERTAMA (Hitung Total Data)
// Kita gunakan $table_name yang dikirim dari file pemanggil
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


// 5. QUERY KEDUA (Ambil Data untuk Halaman Ini)
// Kita gunakan $base_order_by yang dikirim dari file pemanggil
$order_by_sql = $base_order_by . " LIMIT ? OFFSET ?"; 

// Kita pakai SELECT * agar fleksibel untuk tabel apapun
$data_query = "SELECT * FROM " . $table_name . $where_sql . $order_by_sql;

$data_params = $params;
$data_params[] = $limit;
$data_params[] = $offset;
$data_types = $types . "ii"; 

$stmt_data = $conn->prepare($data_query);
$stmt_data->bind_param($data_types, ...$data_params);
$stmt_data->execute();
$result = $stmt_data->get_result();

// $stmt_data JANGAN di-close di sini, karena $result masih akan dipakai di file pemanggil
// Kita akan close di file index.php / riwayat.php setelah selesai looping
?>