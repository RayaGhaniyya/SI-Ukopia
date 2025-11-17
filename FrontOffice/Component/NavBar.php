<?php
// [PERBAIKAN] Cek dulu jika session SUDAH aktif atau BELUM
if (session_status() === PHP_SESSION_NONE) {
    // --- BLOK INI HANYA JALAN JIKA SESSION BELUM DIMULAI ---

    // [PERUBAHAN] Atur masa berlaku session (30 hari)
    // 30 hari * 24 jam * 60 menit * 60 detik = 2,592,000 detik
    $session_lifetime = 2592000;

    // Atur parameter cookie SEBELUM session_start()
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/', // Berlaku untuk seluruh website
        'domain' => '', // (Kosongkan agar default ke domain saat ini)
        'secure' => isset($_SERVER['HTTPS']), // Hanya kirim via HTTPS jika ada
        'httponly' => true // Cookie tidak bisa diakses oleh JavaScript
    ]);


    // [PERUBAHAN] Mulai session
    session_start();
}
// --- Jika session sudah aktif, blok kode di atas akan di-skip ---
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ukopia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <img src="../assets/img/Logo Ukopia/Logo-Ukopia.png" alt="" class="ukopia-logo">

            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../HomePage/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Product/filter.php">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Gallery/index.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Reservation/index.php">Reservations</a>
                    </li>
                    <?php
                    // Cek session login dari file auth/login.php
                    if (isset($_SESSION['customer_uid'])) {
                        // Jika SUDAH LOGIN: Tampilkan link "Profile"
                        // (Saya asumsikan file profile.php Anda ada di folder 'Akun' berdasarkan file auth/login.php)
                        echo '
                    <li class="nav-item">
                        <a class="nav-link" href="../Profile/index.php">Profile</a>
                    </li>';
                    } else {
                        // Jika BELUM LOGIN: Tampilkan link "Sign In"
                        echo '
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php">Sign In</a>
                    </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>