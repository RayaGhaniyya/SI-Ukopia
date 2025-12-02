<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
try {
    $response = [];
    $qProd = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk");
    $response['totalProducts'] = mysqli_fetch_assoc($qProd)['total'];
    $qSales = mysqli_query($conn, "
        SELECT SUM(total_pembayaran) as total 
        FROM transaksi 
        WHERE status_pesanan IN ('Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai')
    ");
    $dSales = mysqli_fetch_assoc($qSales);
    $response['totalSales'] = $dSales['total'] ?? 0;
    $qTop = mysqli_query($conn, "
        SELECT p.nama_produk, SUM(dt.jumlah) as terjual 
        FROM detail_transaksi dt
        JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status_pesanan IN ('Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai')
        GROUP BY p.id_produk
        ORDER BY terjual DESC
        LIMIT 1
    ");
    $dTop = mysqli_fetch_assoc($qTop);
    $response['topProduct'] = $dTop ? $dTop['nama_produk'] : "-";
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
        $status = $row['status_pesanan'];
        $badgeClass = 'badge-secondary'; // Default abu-abu
        if ($status == 'Menunggu Pembayaran') {
            $badgeClass = 'badge-warning'; // Kuning
        } elseif ($status == 'Sudah Dibayar' || $status == 'Diproses') {
            $badgeClass = 'badge-info'; // Biru Muda
        } elseif ($status == 'Dikirim') {
            $badgeClass = 'badge-primary'; // Biru
        } elseif ($status == 'Selesai') {
            $badgeClass = 'badge-success'; // Hijau
        } elseif ($status == 'Batal' || $status == 'Kadaluarsa') {
            $badgeClass = 'badge-danger'; // Merah
        } elseif ($status == 'Pengajuan Batal') {
            $badgeClass = 'badge-danger'; // Merah
        }
        $recentOrders[] = [
            'id' => "#" . $row['id_transaksi'],
            'customer' => $row['customer'],
            'product' => $row['produk_utama'] ?? 'Item dihapus',
            'statusClass' => $badgeClass, // Kirim class warna
            'statusText' => $status      // Kirim teks status asli
        ];
    }
    $response['recentOrders'] = $recentOrders;
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
    $chartWeek = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $label = date('D', strtotime($date));
        $engDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $indDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $label = str_replace($engDays, $indDays, $label);
        $qChart = mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM transaksi 
            WHERE DATE(tanggal_pesan) = '$date' 
            AND status_pesanan IN ('Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai')
        ");
        $val = mysqli_fetch_assoc($qChart)['total'];
        $chartWeek[] = ['label' => $label, 'value' => (int)$val];
    }
    $response['salesData']['week'] = $chartWeek;
    $chartMonth = [];
    $year = date('Y');
    for ($m = 1; $m <= 12; $m++) {
        $monthName = date('M', mktime(0, 0, 0, $m, 1));
        $engMonth = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $indMonth = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthName = str_replace($engMonth, $indMonth, $monthName);
        $qChartM = mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM transaksi 
            WHERE MONTH(tanggal_pesan) = '$m' 
            AND YEAR(tanggal_pesan) = '$year' 
            AND status_pesanan IN ('Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai')
        ");
        $val = mysqli_fetch_assoc($qChartM)['total'];
        $chartMonth[] = ['label' => $monthName, 'value' => (int)$val];
    }
    $response['salesData']['month'] = $chartMonth;
    $topList = [];
    $qTopList = mysqli_query($conn, "
        SELECT p.nama_produk, SUM(dt.jumlah) as sales 
        FROM detail_transaksi dt
        JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
        JOIN produk p ON dp.id_produk = p.id_produk
        JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status_pesanan IN ('Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai')
        GROUP BY p.id_produk
        ORDER BY sales DESC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($qTopList)) {
        $topList[] = ['name' => $row['nama_produk'], 'sales' => (int)$row['sales']];
    }
    $response['topProducts'] = $topList;
    echo json_encode(['status' => 'success', 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

