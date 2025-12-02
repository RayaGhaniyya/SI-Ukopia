<?php
// File: FrontOffice/Product-Checkout/notification.php

require_once '../../Koneksi/koneksi.php';
require_once '../Config/midtrans_config.php';

try {
    // 1. Terima Notifikasi dari Midtrans
    $notif = new \Midtrans\Notification();

    $transaction = $notif->transaction_status;
    $type = $notif->payment_type;
    $order_id = $notif->order_id;
    $fraud = $notif->fraud_status;

    // 2. Tentukan Status Pesanan berdasarkan Laporan Midtrans
    $status = null;

    if ($transaction == 'capture') {
        // Untuk pembayaran kartu kredit
        if ($type == 'credit_card') {
            if ($fraud == 'challenge') {
                $status = 'Menunggu Pembayaran'; // Challenge = meragukan
            } else {
                $status = 'Sudah Dibayar';
            }
        }
    } else if ($transaction == 'settlement') {
        // Uang sudah masuk (Sukses)
        $status = 'Sudah Dibayar';
    } else if ($transaction == 'pending') {
        // Menunggu customer bayar
        $status = 'Menunggu Pembayaran';
    } else if ($transaction == 'deny') {
        // Ditolak
        $status = 'Batal';
    } else if ($transaction == 'expire') {
        // Waktu habis
        $status = 'Kadaluarsa';
    } else if ($transaction == 'cancel') {
        // Dibatalkan
        $status = 'Batal';
    }

    // 3. Update Database Otomatis
    if ($status) {
        // Cari transaksi berdasarkan midtrans_order_id
        $stmt = $conn->prepare("UPDATE transaksi SET status_pesanan = ? WHERE midtrans_order_id = ?");
        $stmt->bind_param("ss", $status, $order_id);
        $stmt->execute();

        // [Opsional] Jika Batal/Expire, kembalikan stok
        if ($status == 'Batal' || $status == 'Kadaluarsa') {
            // Logika kembalikan stok bisa ditaruh di sini mirip cancel_order.php
        }
    }

    // Kirim respons 200 OK ke Midtrans agar tidak dikirim ulang
    http_response_code(200);
    echo "Notification processed";
} catch (Exception $e) {
    http_response_code(500);
    error_log("Midtrans Notification Error: " . $e->getMessage());
    echo "Error";
}
