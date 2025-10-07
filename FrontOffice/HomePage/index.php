<?php
// include("../koneksi/koneksi.php");
include("../Component/Loader.php");
include("../Component/NavBar.php");
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<script src="../assets/js/loader.js"></script>
<link rel="stylesheet" href="../assets/css/homepage.css">

<!-- Hero -->
<div class="hero position-relative">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="../assets/img/Hero-Homepage/heroimg1.png" class="d-block w-100" alt="img 1">
            </div>
            <div class="carousel-item">
                <img src="../assets/img/Hero-Homepage/heroimg2.JPG" class="d-block w-100" alt="Img 2">
            </div>
            <div class="carousel-item">
                <img src="../assets/img/Hero-Homepage/heroimg3.JPG" class="d-block w-100" alt="Img 3">
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <div class="hero-caption">
        <h3>"From Beans To Cups"</h3>
    </div>
</div>

<!-- Gallery -->
<div class="gallery">
    <div class="container py-6">
        <div class="row g-3 pt-5 mb-3">
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 1.JPG" alt="Foto 1" class="img-fluid">
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 2.JPG" alt="Foto 2" class="img-fluid">
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 3.JPG" alt="Foto 3" class="img-fluid">
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 4.JPG" alt="Foto 4" class="img-fluid">
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-3 pb-5">
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 5.JPG" alt="Foto 5" class="img-fluid">
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 6.JPG" alt="Foto 6" class="img-fluid">
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 7.JPG" alt="Foto 7" class="img-fluid">
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="../Gallery/index.php">
                    <div class="gallery-item">
                        <img src="../assets/img/Gallery-Homepage/foto 8.jpg" alt="Foto 8" class="img-fluid">
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Product -->
<div class="products py-5">
    <div class="container">
        <div class="row g-4 text-center">

            <div class="col-md-3 col-sm-6">
                <div class="product-card">
                    <img src="../assets/img/Product-Homepage/arabica.png" alt="Arabica Beans">
                    <div class="product-overlay">
                        <h3>Filter Roast</h3>
                        <a href="../Product/filter.php" class="btn btn-view">View More</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="product-card">
                    <img src="../assets/img/Product-Homepage/robusta.png" alt="Robusta Beans">
                    <div class="product-overlay">
                        <h3>Espresso Roast</h3>
                        <a href="../Product/espresso.php" class="btn btn-view">View More</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="product-card">
                    <img src="../assets/img/Product-Homepage/merchandise.png" alt="Merchandise">
                    <div class="product-overlay">
                        <h3>Merchandise</h3>
                        <a href="../Product/merchandise.php" class="btn btn-view">View More</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="product-card">
                    <img src="../assets/img/Product-Homepage/tools.png" alt="Brewing Tools">
                    <div class="product-overlay">
                        <h3>Brewing Tools</h3>
                        <a href="../Product/tools.php" class="btn btn-view">View More</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- About Section -->
<section class="about py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h2 class="about-title">Tentang Ukopia</h2>
                <p class="about-text">
                    Ukopia adalah ruang seduh yang menghadirkan pengalaman menikmati kopi dengan suasana nyaman
                    dan cita rasa khas. Kami percaya bahwa setiap cangkir kopi memiliki cerita,
                    dan kami siap mendampingi perjalanan kopi Anda.
                </p>
                <p class="about-text">
                    Kami berlokasi di Jember, Jawa Timur, dan siap menyambut Anda untuk sekadar menikmati kopi,
                    berdiskusi, atau bekerja dengan suasana tenang.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.4297838394828!2d113.717165!3d-8.159380200000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd69576aaa91b11%3A0x59c1b9d789699f73!2sRuang%20Seduh%20Ukopia!5e0!3m2!1sid!2sid!4v1758780904181!5m2!1sid!2sid"
                        width="100%"
                        height="350"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Reservation Section -->
<section class="reservation">
    <div class="reservation-card">
        <img src="../assets/img/Reservation/Reservation-Homepage.jpg" alt="Reservation Background">
        <div class="reservation-overlay">
            <h3>Reservation</h3>
            <a href="../Reservation/index.php" class="btn-reserve">Click Here</a>
        </div>
    </div>
</section>

<script src="../assets/js/homepage.js"></script>

<?php
include("../Component/Footer.php");
?>