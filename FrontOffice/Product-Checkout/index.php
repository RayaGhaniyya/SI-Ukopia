<?php
include("../Component/Loader.php");
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<link rel="stylesheet" href="../assets/css/product-checkout.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="../assets/js/loader.js"></script>

<main class="checkout-section">
    <div class="checkout-container">
        <!-- Panel kiri -->
        <div class="checkout-left">
            <button class="back-button" onclick="window.location.href='../Product-Cart/index.php'">
                <i class="fa-solid fa-arrow-left"></i> Checkout
            </button>

            <form class="checkout-form">
                <h3>Contact</h3>
                <input type="email" placeholder="Email or mobile phone number" required>

                <h3>Delivery</h3>
                <div class="delivery-section">
                    <label class="delivery-option">
                        <input type="radio" name="delivery" checked>
                        <div class="option-content">
                            <span>Ship</span>
                            <i class="fa-solid fa-truck"></i>
                        </div>
                    </label>

                    <label class="delivery-option">
                        <input type="radio" name="delivery">
                        <div class="option-content">
                            <span>Pick up</span>
                            <i class="fa-solid fa-store"></i>
                        </div>
                    </label>
                </div>

                <select class="country-select" required>
                    <option value="">Country/Region</option>
                    <option value="Indonesia">Indonesia</option>
                </select>

                <div class="name-fields">
                    <input type="text" placeholder="First name" required>
                    <input type="text" placeholder="Last name" required>
                </div>

                <input type="text" placeholder="Address" required>
                <input type="text" placeholder="Apartment, suite, etc.">
                <div class="city-fields">
                    <input type="text" placeholder="City" required>
                    <input type="text" placeholder="Province" required>
                    <input type="text" placeholder="Postal code" required>
                </div>

                <h3>Metode Pembayaran</h3>
                <div class="payment-method">
                    <i class="fa-solid fa-money-check"></i> Transfer Bank
                    <i class="fa-solid fa-angle-down"></i>
                </div>

                <button type="submit" class="btn-submit">Buat Pesanan</button>
            </form>
        </div>

        <!-- Panel kanan -->
        <div class="checkout-right">
            <h3>Produk Dipesan</h3>
            <div class="product-summary">
                <div class="product-item">
                    <img src="../assets/img/Product-Homepage/arabica.png" alt="">
                    <div>
                        <p>Watermelon Smash</p>
                        <p class="price">Rp 45.000</p>
                    </div>
                    <div class="qty">2</div>
                    <div class="subtotal">Rp 90.000</div>
                </div>

                <div class="product-item">
                    <img src="../assets/img/Product-Homepage/arabica.png" alt="">
                    <div>
                        <p>Watermelon Smash</p>
                        <p class="price">Rp 45.000</p>
                    </div>
                    <div class="qty">1</div>
                    <div class="subtotal">Rp 45.000</div>
                </div>

                <p class="total-products">Total Produk: <strong>3x</strong></p>
            </div>

            <h3>Rincian Pembayaran</h3>
            <div class="payment-summary">
                <p><span>Subtotal Pesanan</span><span>Rp 135.000</span></p>
                <p><span>Subtotal Pengiriman</span><span>Rp 8.000</span></p>
                <p><span>Biaya Layanan</span><span>Rp 2.500</span></p>
                <p class="total"><span>Total Pembayaran</span><span>Rp 145.500</span></p>
            </div>
        </div>
    </div>
</main>

<script src="../assets/js/product-checkout.js"></script>