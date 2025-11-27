<?php
session_start();
include("../../../Koneksi/koneksi.php"); // Sesuaikan path

header('Content-Type: application/json');

// Cek Login
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $response = [];

    // 1. TOTAL PRODUK
    $qProd = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk");
    $response['totalProducts'] = mysqli_fetch_assoc($qProd)['total'];

    // 2. TOTAL PENJUALAN (Omzet - Status Selesai)
    // Kita hitung yang statusnya 'Selesai' (uang masuk)
    $qSales = mysqli_query($conn, "SELECT SUM(total_pembayaran) as total FROM transaksi WHERE status_pesanan = 'Selesai'");
    $dSales = mysqli_fetch_assoc($qSales);
    $response['totalSales'] = $dSales['total'] ?? 0;

    // 3. PRODUK TERLARIS (Top 1)
    // Join detail_transaksi -> detail_produk -> produk
    $qTop = mysqli_query($conn, "
        SELECT p.nama_produk, SUM(dt.jumlah) as terjual 
        FROM detail_transaksi dt
        JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status_pesanan = 'Selesai'
        GROUP BY p.id_produk
        ORDER BY terjual DESC
        LIMIT 1
    ");
    $dTop = mysqli_fetch_assoc($qTop);
    $response['topProduct'] = $dTop ? $dTop['nama_produk'] : "-";

    // 4. PESANAN TERBARU (Limit 5)
    $recentOrders = [];
    $qRecent = mysqli_query($conn, "
        SELECT t.id_transaksi, t.midtrans_order_id, t.status_pesanan, c.nama as customer,
               (SELECT p.nama_produk FROM detail_transaksi dt 
                JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
                JOIN produk p ON dp.id_produk = p.id_produk 
                WHERE dt.id_transaksi = t.id_transaksi LIMIT 1) as produk_utama
        FROM transaksi t
        JOIN akun_customer c ON t.uid_customer = c.uid
        ORDER BY t.tanggal_pesan DESC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($qRecent)) {
        $recentOrders[] = [
            'id' => "#" . $row['id_transaksi'],
            'customer' => $row['customer'],
            'product' => $row['produk_utama'], // Ambil 1 nama produk sbg perwakilan
            'status' => $row['status_pesanan']
        ];
    }
    $response['recentOrders'] = $recentOrders;

    // 5. STOK MENIPIS (Limit 5)
    // Anggap stok < 10 itu kritis
    $lowStock = [];
    $qStock = mysqli_query($conn, "
        SELECT p.nama_produk, dp.stok, s.ukuran 
        FROM detail_produk dp
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN size s ON dp.id_size = s.id_size
        WHERE dp.stok <= 5
        ORDER BY dp.stok ASC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($qStock)) {
        $lowStock[] = [
            'name' => $row['nama_produk'] . ' (' . $row['ukuran'] . ')',
            'stock' => $row['stok'],
            'unit' => 'pcs'
        ];
    }
    $response['lowStock'] = $lowStock;

    // 6. DATA GRAFIK PENJUALAN (MINGGUAN)
    // Loop 7 hari ke belakang
    $chartWeek = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $label = date('D', strtotime($date)); // Mon, Tue, Wed
        
        // Indo Days
        $daysEn = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $daysId = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $label = str_replace($daysEn, $daysId, $label);

        $qChart = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal_pesan) = '$date'");
        $val = mysqli_fetch_assoc($qChart)['total'];
        
        $chartWeek[] = ['label' => $label, 'value' => (int)$val];
    }
    $response['salesData']['week'] = $chartWeek;

    // 7. DATA GRAFIK PENJUALAN (BULANAN)
    // Loop 12 bulan (Jan - Des tahun ini)
    $chartMonth = [];
    $year = date('Y');
    for ($m = 1; $m <= 12; $m++) {
        $monthName = date('M', mktime(0, 0, 0, $m, 1));
        $qChartM = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE MONTH(tanggal_pesan) = '$m' AND YEAR(tanggal_pesan) = '$year'");
        $val = mysqli_fetch_assoc($qChartM)['total'];
        
        $chartMonth[] = ['label' => $monthName, 'value' => (int)$val];
    }
    $response['salesData']['month'] = $chartMonth;

    // 8. TOP 5 PRODUK (Chart Bar Horizontal)
    $topList = [];
    $qTopList = mysqli_query($conn, "
        SELECT p.nama_produk, SUM(dt.jumlah) as sales 
        FROM detail_transaksi dt
        JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status_pesanan = 'Selesai'
        GROUP BY p.id_produk
        ORDER BY sales DESC
        LIMIT 5
    ");
    while($row = mysqli_fetch_assoc($qTopList)) {
        $topList[] = ['name' => $row['nama_produk'], 'sales' => (int)$row['sales']];
    }
    $response['topProducts'] = $topList;

    echo json_encode(['status' => 'success', 'data' => $response]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>