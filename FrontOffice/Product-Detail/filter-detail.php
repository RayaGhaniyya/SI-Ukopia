<?php
// include("../koneksi/koneksi.php");
include("../Component/Loader.php");
// include("../Component/NavBar.php");
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<link rel="stylesheet" href="../assets/css/product-detail.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="../assets/js/loader.js"></script>

<main class="product-detail-section">
    <button class="back-button" onclick="window.location.href='../Product/filter.php'">
        <i class="fa-solid fa-arrow-left"></i>
    </button>

    <table class="product-layout">
        <tr>
            <td class="left-panel">
                <div class="image-container">
                    <img src="../assets/img/Product-Homepage/arabica.png" alt="Watermelon Smash">
                </div>
            </td>

            <td class="right-panel">
                <div class="scroll-area">
                    <div class="content">
                        <h1 class="product-title">Watermelon Smash</h1>
                        <p class="product-price">Rp 200.000,00</p>

                        <p><strong>Origin:</strong> Argopuro, West Java, Indonesia</p>
                        <p><strong>Processing:</strong> Anaerobic Natural</p>
                        <p><strong>Variety:</strong> Typica, Kartika, Lini S & Sigararutang</p>
                        <p><strong>Altitude:</strong> 1500 masl</p>
                        <p><strong>Taste Notes:</strong> Watermelon, Strawberry & Mandarin Orange</p>

                        <div class="product-options">
                            <h4>Grind Size</h4>
                            <button class="active">Whole Beans</button>
                            <button>Espresso</button>
                            <button>Fine</button>
                            <button>Medium</button>
                            <button>Coarse</button>

                            <h4>Size</h4>
                            <button class="active">100 Gr</button>
                            <button>200 Gr</button>
                            <button>500 Gr</button>
                        </div>

                        <div class="product-quantity">
                            <h4>Quantity</h4>
                            <button class="minus">−</button>
                            <span class="count">1</span>
                            <button class="plus">+</button>
                        </div>

                        <p class="stock">Stock: <span id="stock">11</span></p>

                        <div class="product-buttons">
                            <button class="add">Add to Cart</button>
                            <button class="buy">Buy It Now</button>
                        </div>

                        <div class="pickup-info">
                            <p>📍 Pickup available at:</p>
                            <p>Jl. Mastrip No.48, Krajan Timur, Sumbersari, Jember, Jawa Timur</p>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</main>

<script src="../assets/js/product-detail.js"></script>