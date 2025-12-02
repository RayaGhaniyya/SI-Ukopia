<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$id_detail_produk = isset($input['id_detail_produk']) ? intval($input['id_detail_produk']) : 0;
$qty = isset($input['qty']) ? intval($input['qty']) : 1;
if ($id_detail_produk == 0 || $qty <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data produk tidak valid.']);
    exit;
}
$_SESSION['checkout_mode'] = 'buy_now';
$_SESSION['buy_now_item'] = [
    'id_detail_produk' => $id_detail_produk,
    'qty' => $qty
];
echo json_encode(['status' => 'success']);

