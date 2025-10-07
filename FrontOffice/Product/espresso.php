<?php
// include("../koneksi/koneksi.php");
include("../Component/Loader.php");
include("../Component/NavBar.php");
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<script src="../assets/js/loader.js"></script>
<link rel="stylesheet" href="../assets/css/product.css">

<nav class="secondary-navbar">
    <div class="nav-left">
        <a href="/cart" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <div class="dropdown">
            <button class="dropdown-toggle" id="product-btn">
                Product
            </button>
            <div class="dropdown-content" id="product-dropdown">
                <a href="../Product/filter.php">Filter Roast</a>
                <a href="../Product/espresso.php">Espresso Roast</a>
                <a href="../Product/merchandise.php">Merchandise</a>
                <a href="../Product/tools.php">Brewing Tools</a>
                <a href="../Product/approve.php">Ukopia Approve</a>
            </div>
        </div>
    </div>

    <div class="nav-center">
        <h2 class="logo-title">Espresso Roast</h2>
    </div>

    <div class="nav-right">
        <input type="text" class="search-input" placeholder="Search....">
    </div>
</nav>

<main class="product-section">
    <div class="product-grid">

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

        <div class="product-card">
            <img src="https://placehold.co/200x200/2d2d2d/ffffff?text=Kopi" alt="Ijen Carbonic Maceration" class="product-image">
            <div class="product-info">
                <h3 class="product-title">Ijen Carbonic Maceration</h3>
                <p class="product-price">RP. 200.000 IDR</p>
            </div>
        </div>

    </div>
</main>

<script src="../assets/js/product.js"></script>
<?php
include("../Component/Footer.php");
?>