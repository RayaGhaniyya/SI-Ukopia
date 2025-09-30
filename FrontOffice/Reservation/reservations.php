<?php include("../Component/NavBar.php"); ?>
<link rel="stylesheet" href="../assets/css/reservation.css">

<section class="reservation-section">
    <!-- Kiri: Deskripsi -->
    <div class="left-content">
        <div class="overlay"></div>
        <div class="text-box">
            <h1>Ruang Seduh Ukopia</h1>
            <h2>Tempat Kopi, Cerita, dan Inspirasi Bertemu</h2>
            <p>
                Ruang Seduh Ukopia bukan sekadar tempat minum kopi. Ini adalah ruang ngobrol, belajar, dan ngerasain kopi dengan cara yang lebih personal.
                Kami bikin tempat ini buat kamu yang suka suasana tenang, hangat, dan nyaman. Di sini kamu bisa duduk santai, nyeruput kopi pelan-pelan,
                sambil ngobrol atau sekadar menikmati waktu.
            </p>
        </div>
    </div>

    <!-- Kanan: Form Reservasi -->
    <div class="right-content">
        <div class="overlay"></div>
        <div class="form-box">
            <h2>Reservations</h2>
            <form>
                <!-- Nama & Telepon -->
                <div class="input-group">
                    <input type="text" placeholder="Name" required>
                    <input type="text" placeholder="No Telepon" required>
                </div>

                <!-- Hari -->
                <div class="days">
                    <button type="button">1</button>
                    <button type="button">2</button>
                    <button type="button">3</button>
                    <button type="button">4</button>
                    <button type="button">5</button>
                    <button type="button">6</button>
                    <button type="button">7</button>
                </div>

                <!-- Time Picker -->
                <div class="time-select">
                    <!-- Dropdown Jam -->
                    <div class="custom-dropdown">
                        <button type="button" class="dropdown-btn">07</button>
                        <ul class="dropdown-list">
                            <li>10</li>
                            <li>11</li>
                            <li>12</li>
                            <li>13</li>
                            <li>14</li>
                            <li>15</li>
                            <li>16</li>
                            <li>17</li>
                            <li>18</li>
                            <li>19</li>
                        </ul>
                    </div>

                    <span>:</span>

                    <!-- Dropdown Menit -->
                    <div class="custom-dropdown">
                        <button type="button" class="dropdown-btn">00</button>
                        <ul class="dropdown-list">
                            <li>00</li>
                            <li>10</li>
                            <li>20</li>
                            <li>30</li>
                            <li>40</li>
                            <li>50</li>
                        </ul>
                    </div>
                </div>

                <!-- Tombol Confirm -->
                <button type="submit" class="confirm-btn">Confirm</button>
            </form>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll(".custom-dropdown").forEach(drop => {
        const btn = drop.querySelector(".dropdown-btn");
        const list = drop.querySelectorAll(".dropdown-list li");

        btn.addEventListener("click", () => {
            drop.classList.toggle("active");
        });

        list.forEach(item => {
            item.addEventListener("click", () => {
                btn.textContent = item.textContent;
                drop.classList.remove("active");
            });
        });
    });
</script>

<?php include("../Component/Footer.php"); ?>