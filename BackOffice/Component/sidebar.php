<link rel="stylesheet" href="../assets/css/sidebar.css">

<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <img src="../assets/img/Logo/Logo-Ukopia.png" alt="Ukopia Logo" class="logo">
        <button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button>
    </div>

    <ul class="nav-list">
        <li><a href="../Dashboard/index.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="productBtn">
                <i class="fas fa-store"></i><span>MarketPlace</span>
            </a>
            <ul class="dropdown-menu" id="productDropdown">
                <li><a href="#"><i class="fas fa-box-open"></i><span>Product</span></a></li>
                <li><a href="#"><i class="fas fa-grip-horizontal"></i><span>Grind Size</span></a></li>
                <li><a href="#"><i class="fas fa-ruler-combined"></i><span>Size</span></a></li>
                <li><a href="#"><i class="fas fa-tags"></i><span>Kategori</span></a></li>
                <li><a href="#"><i class="fas fa-check-circle"></i><span>Stok</span></a></li>
            </ul>
        </li>

        <li><a href="#"><i class="fas fa-calendar-check"></i><span>Reservation</span></a></li>
        <li><a href="../Gallery/index.php"><i class="fas fa-image"></i><span>Gallery</span></a></li>
        <li><a href="#"><i class="fas fa-receipt"></i><span>Riwayat Transaksi</span></a></li>
    </ul>

    <div class="profile-section">
        <a href="#"><i class="fas fa-user-circle"></i><span>Profile</span></a>
    </div>
</div>

<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/global.js"></script>