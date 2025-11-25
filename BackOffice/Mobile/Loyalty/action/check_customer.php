<?php
include("../../../../Koneksi/koneksi.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$keyword = trim($_POST['keyword']);

if (empty($keyword)) {
    $_SESSION['error'] = "Kolom pencarian tidak boleh kosong!";
    header("Location: ../index.php");
    exit;
}

// LOGIKA PENCARIAN (OR)
// Mencari berdasarkan no_telpon ATAU username
$stmt = $conn->prepare("SELECT uid, nama FROM akun_customer WHERE no_telpon = ? OR username = ?");
$stmt->bind_param("ss", $keyword, $keyword);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // KASUS: User Ditemukan
    // Redirect ke halaman input menu dengan membawa ID User
    header("Location: ../add.php?uid=" . $user['uid']);
    exit;
} else {
    // KASUS: User Tidak Ditemukan
    $_SESSION['error'] = "Customer dengan No. Telpon / Username '<b>$keyword</b>' tidak ditemukan!";
    header("Location: ../index.php");
    exit;
}

$stmt->close();
$conn->close();
?>