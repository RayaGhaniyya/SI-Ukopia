<link rel="stylesheet" href="/SI-Ukopia/BackOffice/assets/css/sidebar.css">

<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <img src="/SI-Ukopia/BackOffice/assets/img/Logo/Logo-Ukopia.png" alt="Ukopia Logo" class="logo">
        <button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button>
    </div>

    <ul class="nav-list">
        <li>
            <a href="/SI-Ukopia/BackOffice/Dashboard/index.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="productBtn">
                <i class="fas fa-store"></i>
                <span>MarketPlace</span>
            </a>
            <ul class="dropdown-menu" id="productDropdown">
                <li>
                    <a href="#">
                        <i class="fas fa-box-open"></i>
                        <span>Product</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-check-circle"></i>
                        <span>Stok</span>
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-calendar-check"></i>
                <span>Reservation</span>
            </a>
        </li>

        <li>
            <a href="/SI-Ukopia/BackOffice/Gallery/index.php">
                <i class="fas fa-image"></i>
                <span>Gallery</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-receipt"></i>
                <span>Riwayat Transaksi</span>
            </a>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="mobileBtn">
                <i class="fas fa-list-alt"></i>
                <span>List Data</span>
            </a>
            <ul class="dropdown-menu" id="mobileDropdown">
                <li>
                    <a href="#">
                        <i class="fas fa-grip-horizontal"></i>
                        <span>Grind Size</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-ruler-combined"></i>
                        <span>Size</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-utensils"></i>
                        <span>Kategori Menu</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-tools"></i>
                        <span>Kategori Alat</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-signal"></i>
                        <span>Status</span>
                    </a>
                </li>
            </ul>
        </li>


        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="mobileBtn">
                <i class="fas fa-mobile-screen"></i>
                <span>Mobile</span>
            </a>
            <ul class="dropdown-menu" id="mobileDropdown">
                <li>
                    <a href="/SI-Ukopia/BackOffice/Mobile/Menu/index.php">
                        <i class="fas fa-mug-hot"></i>
                        <span>Menu</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>

    <div class="profile-section">
        <a href="#">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
    </div>
</div>

<script src="/SI-Ukopia/BackOffice/assets/js/sidebar.js"></script>
<script src="/SI-Ukopia/BackOffice/assets/js/global.js"></script>