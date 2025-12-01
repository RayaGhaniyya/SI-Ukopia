<?php
// [PERBAIKAN] Cek dulu jika session SUDAH aktif atau BELUM
if (session_status() === PHP_SESSION_NONE) {
    // --- BLOK INI HANYA JALAN JIKA SESSION BELUM DIMULAI ---

    // [PERUBAHAN] Atur masa berlaku session (30 hari)
    $session_lifetime = 2592000;

    // Atur parameter cookie SEBELUM session_start()
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/', 
        'domain' => '', 
        'secure' => isset($_SERVER['HTTPS']), 
        'httponly' => true 
    ]);

    // [PERUBAHAN] Mulai session
    session_start();
}
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
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="../HomePage/index.php">
                <img src="../assets/img/Logo Ukopia/Logo-Ukopia.png" alt="Ukopia" class="ukopia-logo">
            </a>

            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav position-absolute start-50 translate-middle-x navbar-center">
                    <li class="nav-item">
                        <a class="nav-link nav-link-animate" href="../HomePage/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-animate" href="../Product/filter.php">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-animate" href="../Gallery/index.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-animate" href="../Reservation/index.php">Resservations</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center navbar-icons">
                    <?php
                    // LOGIKA BUTTON KHUSUS MEMBER (LOGIN)
                    if (isset($_SESSION['customer_uid'])) {
                        // Jika SUDAH LOGIN: Tampilkan 3 Icon (Cart, Orders, Profile)
                        echo '
                        <li class="nav-item">
                            <a class="nav-link nav-icon" href="../Product-Cart/index.php" title="Cart">
                                <i class="fas fa-shopping-cart"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-icon" href="../Orders/index.php" title="My Orders">
                                <i class="fas fa-box"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-icon" href="../Profile/index.php" title="Profile">
                                <i class="fas fa-user"></i>
                            </a>
                        </li>';
                    } else {
                        // Jika BELUM LOGIN: Hanya Tampilkan Icon Login
                        echo '
                        <li class="nav-item">
                            <a class="nav-link nav-icon" href="../auth/login.php" title="Sign In">
                                <i class="fas fa-user"></i>
                            </a>
                        </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

