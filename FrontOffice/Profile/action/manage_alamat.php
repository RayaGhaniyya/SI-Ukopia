<?php
session_start();
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if (!isset($_SESSION['customer_uid'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi habis.']);
    exit;
}
$uid = $_SESSION['customer_uid'];
$id_alamat = isset($_POST['id_alamat']) ? intval($_POST['id_alamat']) : 0;
$label = $_POST['label_alamat'] ?? '';
$penerima = $_POST['nama_penerima'] ?? '';
$telp = $_POST['no_telepon'] ?? '';
$kodepos = $_POST['kode_pos'] ?? '';
$kota = $_POST['kota'] ?? '';
$provinsi = $_POST['provinsi'] ?? '';
$detail = $_POST['alamat_lengkap'] ?? '';
$is_utama = isset($_POST['is_utama']) ? 1 : 0;

if (empty($label) || empty($penerima) || empty($telp) || empty($detail)) {
    echo json_encode(['success' => false, 'message' => 'Data wajib diisi.']);
    exit;
}

$conn->begin_transaction();
try {
    if ($is_utama == 1) {
        $conn->query("UPDATE alamat_customer SET is_utama = 0 WHERE uid_customer = '$uid'");
    }

    if ($id_alamat > 0) {
        $stmt = $conn->prepare("UPDATE alamat_customer SET label_alamat=?, nama_penerima=?, no_telepon=?, alamat_lengkap=?, kota=?, provinsi=?, kode_pos=?, is_utama=? WHERE id_alamat=? AND uid_customer=?");
        $stmt->bind_param("sssssssiii", $label, $penerima, $telp, $detail, $kota, $provinsi, $kodepos, $is_utama, $id_alamat, $uid);
        $msg = "Alamat berhasil diperbarui!";
    } else {
        $stmt = $conn->prepare("INSERT INTO alamat_customer (uid_customer, label_alamat, nama_penerima, no_telepon, alamat_lengkap, kota, provinsi, kode_pos, is_utama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssi", $uid, $label, $penerima, $telp, $detail, $kota, $provinsi, $kodepos, $is_utama);
        $msg = "Alamat baru berhasil disimpan!";
    }

    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        throw new Exception("Gagal menyimpan data.");
    }
    $stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
$conn->close();
