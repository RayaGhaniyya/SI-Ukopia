<?php
// include("../koneksi/koneksi.php");
include("../Component/Loader.php");
// include("../Component/NavBar.php");
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<link rel="stylesheet" href="../assets/css/product-cart.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="../assets/js/loader.js"></script>

<main class="cart-section">
    <div class="cart-header">
        Your Cart
        <a href="../Product/filter.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>

    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- Produk 1 -->
            <tr class="cart-item">
                <td>
                    <div class="product-info">
                        <input type="checkbox" checked class="custom-checkbox">
                        <div class="product-img">
                            <img src="../assets/img/Product-Homepage/arabica.png" alt="Watermelon Smash">
                        </div>
                        <div class="product-details">
                            <h3>Watermelon Smash</h3>
                            <p>Rp 200.000,00</p>
                            <p><strong>Size:</strong> 100 Gram</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="quantity">
                        <button class="qty-btn">−</button>
                        <span class="qty-count">1</span>
                        <button class="qty-btn">+</button>
                        <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
                <td class="product-total">Rp 200.000,00</td>
            </tr>

            <!-- Produk 2 -->
            <tr class="cart-item">
                <td>
                    <div class="product-info">
                        <input type="checkbox" class="custom-checkbox">
                        <div class="product-img">
                            <img src="../assets/img/Product-Homepage/robusta.png" alt="Robusta Classic">
                        </div>
                        <div class="product-details">
                            <h3>Robusta Classic</h3>
                            <p>Rp 150.000,00</p>
                            <p><strong>Size:</strong> 250 Gram</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="quantity">
                        <button class="qty-btn">−</button>
                        <span class="qty-count">2</span>
                        <button class="qty-btn">+</button>
                        <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
                <td class="product-total">Rp 300.000,00</td>
            </tr>

            <!-- Produk 3 -->
            <tr class="cart-item">
                <td>
                    <div class="product-info">
                        <input type="checkbox" class="custom-checkbox">
                        <div class="product-img">
                            <img src="../assets/img/Product-Homepage/merchandise.png" alt="Liberica Bold">
                        </div>
                        <div class="product-details">
                            <h3>Merchandise Ukopia</h3>
                            <p>Rp 150.000,00</p>
                            <p><strong>Size:</strong> XL</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="quantity">
                        <button class="qty-btn">−</button>
                        <span class="qty-count">1</span>
                        <button class="qty-btn">+</button>
                        <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
                <td class="product-total">Rp 150.000,00</td>
            </tr>
        </tbody>
    </table>

    <div class="cart-summary">
        <div class="summary-left">
            <input type="checkbox" id="select-all" class="custom-checkbox">
            <label for="select-all" class="select-all-label">Pilih Semua Produk</label>
        </div>
        <div class="summary-center">
            <p><strong>Estimated total:</strong> <span id="cart-total">Rp 750.000,00</span></p>
        </div>
        <div class="summary-right">
            <button class="checkout-btn">Check out</button>
        </div>
    </div>
</main>

<script src="../assets/js/product-cart.js"></script>