<?php
session_start();
include("../../Koneksi/koneksi.php"); // Path 2x ../

// (1) Cek apakah email user ada di session
// (Jika tidak ada, user tidak bisa asal buka halaman ini)
if (!isset($_SESSION['verification_email'])) {
    // Jika tidak ada email, tendang ke login
    header('Location: login.php');
    exit;
}
$email = $_SESSION['verification_email'];

// (2) Ambil pesan notifikasi (jika ada)
$error_message = $_GET['error'] ?? '';
$success_message = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda - Ukopia</title>
    <link rel="stylesheet" href="../assets/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="../assets/js/global.js?v=<?php echo time(); ?>"></script>

    <style>
        /* CSS Tambahan untuk tombol disabled dan countdown */
        .auth-btn.disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .auth-btn.disabled:hover {
            background: #ccc;
        }

        #countdown-timer {
            font-size: 0.9rem;
            color: #888;
            margin-top: 10px;
            display: block;
            /* Agar muncul di bawah tombol */
        }
    </style>
</head>

<body>

    <div class="verify-wrapper">

        <div class="verification-status">

            <div class="verification-icon" style="background: #f59e0b;"> <i class="fas fa-envelope"></i>
            </div>

            <h2>Cek Email Anda</h2>
            <p>
                Kami telah mengirimkan link verifikasi ke
                <strong style="color: #111;"><?= htmlspecialchars($email) ?></strong>.
                Silakan cek inbox (atau folder spam) Anda.
            </p>

            <a href="action/resend_code.php" class="auth-btn" id="resend-button" style="text-align: center; text-decoration: none; margin-top: 10px;">
                Kirim Ulang Kode
            </a>

            <span id="countdown-timer"></span>

        </div>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const resendButton = document.getElementById('resend-button');
            const countdownTimer = document.getElementById('countdown-timer');
            let cooldown = 60; // 60 detik

            function startCooldown() {
                // 1. Nonaktifkan tombol
                resendButton.classList.add('disabled');
                resendButton.style.pointerEvents = 'none'; // Matikan link

                countdownTimer.textContent = `Bisa kirim ulang dalam ${cooldown} detik...`;

                // 2. Mulai hitungan mundur
                const interval = setInterval(() => {
                    cooldown--;
                    countdownTimer.textContent = `Bisa kirim ulang dalam ${cooldown} detik...`;

                    if (cooldown <= 0) {
                        clearInterval(interval);
                        // 3. Aktifkan tombol lagi
                        countdownTimer.textContent = '';
                        resendButton.classList.remove('disabled');
                        resendButton.style.pointerEvents = 'auto'; // Hidupkan link
                        localStorage.removeItem('cooldown_start'); // Hapus timestamp
                    }
                }, 1000); // 1 detik
            }

            // Cek jika notifikasi (dari PHP) ada
            <?php if (!empty($success_message)): ?>
                showNotification('<?= $success_message ?>', 'success');
                startCooldown(); // Mulai cooldown jika baru kirim ulang
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                showNotification('<?= $error_message ?>', 'error');
            <?php endif; ?>

            // Cek jika kita baru klik "Kirim Ulang"
            resendButton.addEventListener('click', function(e) {
                if (resendButton.classList.contains('disabled')) {
                    e.preventDefault(); // Hentikan klik jika masih cooldown
                    showNotification('Harap tunggu 60 detik sebelum mengirim ulang.', 'warning');
                } else {
                    // (Jika tidak cooldown, biarkan link berjalan ke 'resend_code.php')
                    // (Kita tidak panggil startCooldown() di sini, 
                    // tapi di 'DOMContentLoaded' saat halaman di-reload)
                }
            });

            // Cek jika halaman di-refresh saat masih cooldown
            const cooldownStart = localStorage.getItem('cooldown_start');
            if (cooldownStart) {
                const timePassed = Math.floor((Date.now() - cooldownStart) / 1000);
                if (timePassed < 60) {
                    cooldown = 60 - timePassed;
                    startCooldown();
                } else {
                    localStorage.removeItem('cooldown_start');
                }
            } else {
                // Jika ini pertama kali, mulai cooldown
                localStorage.setItem('cooldown_start', Date.now());
                startCooldown();
            }
        });
    </script>

</body>

</html>