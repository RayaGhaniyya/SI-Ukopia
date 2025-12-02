<?php

require_once '../../Koneksi/koneksi.php';
require_once '../Config/midtrans_config.php';

try {
    $notif = new \Midtrans\Notification();

    $transaction = $notif->transaction_status;
    $type = $notif->payment_type;
    $order_id = $notif->order_id;
    $fraud = $notif->fraud_status;

    $status = null;

    if ($transaction == 'capture') {
        if ($type == 'credit_card') {
            if ($fraud == 'challenge') {
                $status = 'Menunggu Pembayaran'; // Challenge = meragukan
            } else {
                $status = 'Sudah Dibayar';
            }
        }
    } else if ($transaction == 'settlement') {
        $status = 'Sudah Dibayar';
    } else if ($transaction == 'pending') {
        $status = 'Menunggu Pembayaran';
    } else if ($transaction == 'deny') {
        $status = 'Batal';
    } else if ($transaction == 'expire') {
        $status = 'Kadaluarsa';
    } else if ($transaction == 'cancel') {
        $status = 'Batal';
    }

    if ($status) {
        $stmt = $conn->prepare("UPDATE transaksi SET status_pesanan = ? WHERE midtrans_order_id = ?");
        $stmt->bind_param("ss", $status, $order_id);
        $stmt->execute();

        if ($status == 'Batal' || $status == 'Kadaluarsa') {
        }
    }

    http_response_code(200);
    echo "Notification processed";
} catch (Exception $e) {
    http_response_code(500);
    error_log("Midtrans Notification Error: " . $e->getMessage());
    echo "Error";
}

