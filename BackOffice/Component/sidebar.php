<link rel="stylesheet" href="/SI-Ukopia/BackOffice/assets/css/sidebar.css">
<link rel="stylesheet" href="/SI-Ukopia/BackOffice/assets/css/Responsive/sidebar-responsive.css">

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

        <li>
            <a href="/SI-Ukopia/BackOffice/Transaksi/index.php">
                <i class="fas fa-receipt"></i>
                <span>Transaksi</span>
            </a>
        </li>

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="productBtn"> <i class="fas fa-box-open"></i> <span>Manajemen Produk</span>
            </a>
            <ul class="dropdown-menu" id="productDropdown">
                <li>
                    <a href="/SI-Ukopia/BackOffice/Product/ProductBeans/index.php">
                        <i class="fas fa-coffee"></i> <span>Beans</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/Product/ProductMerch/index.php">
                        <i class="fas fa-tshirt"></i> <span>Merchandise</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/Product/ProductTools/index.php">
                        <i class="fas fa-tools"></i> <span>Brewing Tools & Lainnya</span>
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="/SI-Ukopia/BackOffice/Reservation/index.php">
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

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="mobileBtn"> <i class="fas fa-list-alt"></i>
                <span>List Data</span>
            </a>
            <ul class="dropdown-menu" id="mobileDropdown">
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Alat/index.php">
                        <i class="fas fa-toolbox"></i>
                        <span>Alat</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Grind_Size/index.php">
                        <i class="fas fa-grip-horizontal"></i>
                        <span>Grind Size</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Size/index.php">
                        <i class="fas fa-ruler-combined"></i>
                        <span>Size</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Metode/index.php">
                        <i class="fas fa-flask"></i>
                        <span>Metode</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Kategori/index.php">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Kategori_Menu/index.php">
                        <i class="fas fa-utensils"></i>
                        <span>Kategori Menu</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/List_Data/Kategori_Alat/index.php">
                        <i class="fas fa-tools"></i>
                        <span>Kategori Alat</span>
                    </a>
                </li>
            </ul>
        </li>


        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="mobileBtn"> <i class="fas fa-mobile-screen"></i>
                <span>Mobile</span>
            </a>
            <ul class="dropdown-menu" id="mobileDropdown">
                <li>
                    <a href="/SI-Ukopia/BackOffice/Mobile/Menu/index.php">
                        <i class="fas fa-mug-hot"></i>
                        <span>Menu</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/Mobile/Resep/index.php">
                        <i class="fas fa-book-open"></i>
                        <span>Resep</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/Mobile/Reward/klaim.php">
                        <i class="fas fa-gift"></i>
                        <span>Reward</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/Mobile/Loyalty/index.php">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Loyalty</span>
                    </a>
                </li>
                <li>
                    <a href="/SI-Ukopia/BackOffice/Mobile/Promo/index.php">
                        <i class="fas fa-percent"></i>
                        <span>Promo</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>

    <div class="profile-section">

        <a href="/SI-Ukopia/BackOffice/Auth/indexlogout.php" class="logout-btn" title="Keluar">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

</div>