<?php
// Aktifkan error reporting untuk debugging (Hapus/Komentari saat production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// [PENTING] Perbaiki Path Include
// Naik 2 level dari Product-Checkout -> FrontOffice -> Root -> Koneksi
include("../../Koneksi/koneksi.php");

// Naik 1 level dari Product-Checkout -> FrontOffice -> Config
// Pastikan folder 'Config' ada di dalam 'FrontOffice' dan nama filenya benar
require_once '../Config/midtrans_config.php';

header('Content-Type: application/json');

// 1. Cek Login
if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis. Silakan login ulang.']);
    exit;
}

$uid = $_SESSION['customer_uid'];
$input = json_decode(file_get_contents('php://input'), true);

// 2. Ambil Data Input
$id_alamat = isset($input['id_alamat']) ? intval($input['id_alamat']) : 0;
$ongkir = isset($input['ongkir']) ? intval($input['ongkir']) : 0;
// GANTI: Sesuaikan biaya layanan dengan tampilan di index.php
$biaya_layanan = 2500;

if ($id_alamat == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Alamat pengiriman belum dipilih.']);
    exit;
}

try {
    // 3. Ambil Data Customer untuk Midtrans
    $queryCust = mysqli_query($conn, "SELECT nama, email, username FROM akun_customer WHERE uid = '$uid'");
    if (!$queryCust) throw new Exception("Gagal mengambil data customer.");
    $custData = mysqli_fetch_assoc($queryCust);
    $email_cust = !empty($custData['email']) ? $custData['email'] : 'customer@ukopia.com';

    // 4. Siapkan Item Belanja
    $items_beli = [];
    $subtotal = 0;

    // Cek Mode (Buy Now vs Keranjang)
    if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'buy_now' && isset($_SESSION['buy_now_item'])) {
        // MODE BUY NOW
        $id_detail = $_SESSION['buy_now_item']['id_detail_produk'];
        $qty = $_SESSION['buy_now_item']['qty'];

        $q = mysqli_query($conn, "SELECT dp.*, p.nama_produk FROM detail_produk dp JOIN produk p ON dp.id_produk = p.id_produk WHERE dp.id_detail_produk = '$id_detail'");
        $row = mysqli_fetch_assoc($q);

        $items_beli[] = [
            'id' => $id_detail,
            'price' => intval($row['harga']),
            'quantity' => intval($qty),
            'name' => substr($row['nama_produk'], 0, 50)
        ];
        $subtotal += ($row['harga'] * $qty);
    } else {
        // MODE KERANJANG
        $q = mysqli_query($conn, "SELECT k.jumlah, dp.id_detail_produk, dp.harga, p.nama_produk FROM keranjang k JOIN detail_produk dp ON k.id_detail_produk = dp.id_detail_produk JOIN produk p ON dp.id_produk = p.id_produk WHERE k.uid_akun = '$uid'");

        while ($row = mysqli_fetch_assoc($q)) {
            $items_beli[] = [
                'id' => $row['id_detail_produk'],
                'price' => intval($row['harga']),
                'quantity' => intval($row['jumlah']),
                'name' => substr($row['nama_produk'], 0, 50)
            ];
            $subtotal += ($row['harga'] * $row['jumlah']);
        }
    }

    // Tambahkan Ongkir & Layanan ke Detail Midtrans
    if ($ongkir > 0) {
        $items_beli[] = ['id' => 'SHIP', 'price' => $ongkir, 'quantity' => 1, 'name' => 'Ongkos Kirim'];
    }
    $items_beli[] = ['id' => 'SRV', 'price' => $biaya_layanan, 'quantity' => 1, 'name' => 'Biaya Layanan'];

    $total_bayar = $subtotal + $ongkir + $biaya_layanan;
    $midtrans_order_id = "TRX-" . time() . "-" . rand(100, 999);

    // 5. Simpan ke Database
    $conn->begin_transaction();

    // A. Insert Transaksi
    $stmt = $conn->prepare("INSERT INTO transaksi (uid_customer, id_alamat_kirim, total_harga_barang, ongkir, total_pembayaran, status_pesanan, midtrans_order_id) VALUES (?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?)");
    $stmt->bind_param("iiiiis", $uid, $id_alamat, $subtotal, $ongkir, $total_bayar, $midtrans_order_id);
    $stmt->execute();
    $id_transaksi_baru = $stmt->insert_id;
    $stmt->close();

    // B. Insert Detail Transaksi
    $stmt_det = $conn->prepare("INSERT INTO detail_transaksi (id_transaksi, id_detail_produk, jumlah, harga_saat_beli) VALUES (?, ?, ?, ?)");
    foreach ($items_beli as $item) {
        if ($item['id'] == 'SHIP' || $item['id'] == 'SRV') continue;

        $stmt_det->bind_param("iiii", $id_transaksi_baru, $item['id'], $item['quantity'], $item['price']);
        $stmt_det->execute();

        // Kurangi Stok
        $conn->query("UPDATE detail_produk SET stok = stok - {$item['quantity']} WHERE id_detail_produk = {$item['id']}");
    }
    $stmt_det->close();

    // C. Hapus Keranjang (Kecuali mode Buy Now)
    if (!isset($_SESSION['checkout_mode'])) {
        $conn->query("DELETE FROM keranjang WHERE uid_akun = '$uid'");
    }

    // D. Minta Snap Token ke Midtrans
    $params = [
        'transaction_details' => [
            'order_id' => $midtrans_order_id,
            'gross_amount' => $total_bayar,
        ],
        'item_details' => $items_beli,
        'customer_details' => [
            'first_name' => $custData['nama'],
            'email' => $email_cust,
        ]
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($params);

    // E. Update Token ke Database
    $conn->query("UPDATE transaksi SET snap_token = '$snapToken' WHERE id_transaksi = '$id_transaksi_baru'");

    $conn->commit();

    // Bersihkan Session Buy Now
    unset($_SESSION['checkout_mode']);
    unset($_SESSION['buy_now_item']);

    echo json_encode(['status' => 'success', 'token' => $snapToken, 'order_id' => $midtrans_order_id]);
} catch (Exception $e) {
    $conn->rollback();
    // Log error untuk debugging server-side
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Transaksi Gagal: ' . $e->getMessage()]);
}
