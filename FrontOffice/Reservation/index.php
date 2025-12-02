<?php
include("../Component/Loader.php");
include("../Component/NavBar.php");
include("../../Koneksi/koneksi.php"); // koneksi database

date_default_timezone_set('Asia/Jakarta');

$total_jam_operasional = 13;

$dates = [];
$today = new DateTime(); // Tanggal hari ini
$today_str = $today->format('Y-m-d'); // String 'YYYY-MM-DD' hari ini

$day_of_week = (int)$today->format('w');
$start_of_week = clone $today;
$start_of_week->modify("-$day_of_week days"); // Mundur ke hari Minggu

for ($i = 0; $i < 7; $i++) {
    $date = clone $start_of_week;
    $date->modify("+$i days");
    $date_str = $date->format('Y-m-d');

    $is_past = ($date_str < $today_str); // Cek apakah tanggal sudah lewat

    $dates[] = [
        'value' => $date_str,             // 2025-11-05
        'dayNum' => $date->format('j M'), // 5 Nov
        'isDisabled' => $is_past,         // true jika sudah lewat
        'isFullyBooked' => false          // Flag untuk cek penuh
    ];
}

$start_date_query = $dates[0]['value'];
$end_date_query = $dates[6]['value'];

$bookedHours = [];
$stmt = $conn->prepare("SELECT tanggal, jam FROM reservasi WHERE status = 'Confirmed' AND tanggal BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date_query, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tanggal = $row['tanggal'];
    $jam_H = date('H', strtotime($row['jam']));
    if (!isset($bookedHours[$tanggal])) {
        $bookedHours[$tanggal] = [];
    }
    $bookedHours[$tanggal][$jam_H] = true;
}
$stmt->close();

foreach ($dates as $index => $date) {
    $date_str = $date['value'];
    if (isset($bookedHours[$date_str])) {
        $jumlah_jam_booked = count($bookedHours[$date_str]);

        if ($jumlah_jam_booked >= $total_jam_operasional) {
            $dates[$index]['isFullyBooked'] = true;
            $dates[$index]['isDisabled'] = true; // Otomatis nonaktifkan
        }
    }
}

$default_selected_day = '';
foreach ($dates as $date) {
    if (!$date['isDisabled']) {
        $default_selected_day = $date['value'];
        break;
    }
}

if (empty($default_selected_day)) {
    $today_in_array = false;
    foreach ($dates as $date) {
        if ($date['value'] == $today_str) {
            $default_selected_day = $date['value'];
            $today_in_array = true;
            break;
        }
    }
    if (!$today_in_array && !empty($dates)) {
        $default_selected_day = $dates[0]['value'];
    }
}
?>

<link rel="stylesheet" href="../assets/css/loader.css">
<link rel="stylesheet" href="../assets/css/toast.css">
<link rel="stylesheet" href="../assets/css/reservation.css">

<script src="../assets/js/loader.js"></script>

<script>
    const bookedHours = <?php echo json_encode($bookedHours); ?>;
</script>

<script>
    const serverTime = {
        todayDateStr: "<?php echo $today_str; ?>", // "2025-11-05"
        currentHour: <?php echo (int)date('H'); ?>, // Misal: 12
        currentMinute: <?php echo (int)date('i'); ?> // Misal: 45
    };
</script>
<section class="reservation-section">
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

    <div class="right-content">
        <div class="overlay"></div>
        <div class="form-box">
            <h2>Reservations</h2>

            <form id="reservationForm">
                <div class="input-group">
                    <input type="text" name="nama_pelanggan" placeholder="Name" required>
                    <input type="text" name="no_telepon" placeholder="No Telepon" required>
                </div>

                <div class="days">
                    <?php foreach ($dates as $date): ?>
                        <button type="button"
                            class="day-btn <?php echo ($date['value'] == $default_selected_day) ? 'active' : ''; ?>"
                            data-value="<?php echo $date['value']; ?>"
                            <?php
                            if ($date['isDisabled']) echo 'disabled';

                            if ($date['isFullyBooked']) {
                                echo ' title="Slot Penuh"';
                            } elseif ($date['isDisabled']) {
                                echo ' title="Tanggal sudah lewat"';
                            }
                            ?>>
                            <?php echo $date['dayNum']; ?>
                        </button>
                    <?php endforeach; ?>

                    <input type="hidden" name="tanggal" id="selectedDay" value="<?php echo $default_selected_day; ?>" required>
                </div>

                <div class="time-select">
                    <div class="custom-dropdown">
                        <button type="button" class="dropdown-btn" id="hourBtn">10</button>
                        <ul class="dropdown-list hour-list">
                            <?php for ($h = 10; $h <= 22; $h++): ?>
                                <li data-value="<?= str_pad($h, 2, "0", STR_PAD_LEFT) ?>"><?= str_pad($h, 2, "0", STR_PAD_LEFT) ?></li>
                            <?php endfor; ?>
                        </ul>
                    </div>

                    <span>:</span>

                    <div class="custom-dropdown">
                        <button type="button" class="dropdown-btn" id="minuteBtn">00</button>
                        <ul class="dropdown-list minute-list">
                            <?php for ($m = 0; $m < 60; $m += 10): ?>
                                <li data-value="<?= str_pad($m, 2, "0", STR_PAD_LEFT) ?>"><?= str_pad($m, 2, "0", STR_PAD_LEFT) ?></li>
                            <?php endfor; ?>
                        </ul>
                    </div>

                    <input type="hidden" name="jam" id="selectedTime" value="10:00:00" required>
                </div>

                <button type="submit" id="confirmBtn" class="confirm-btn">Confirm</button>
            </form>
        </div>
    </div>
</section>

<script src="../assets/js/toast.js"></script>
<script src="../assets/js/reservation.js"></script>
<?php include("../Component/Footer.php"); ?>
