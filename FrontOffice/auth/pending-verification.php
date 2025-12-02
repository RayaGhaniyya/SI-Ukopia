<?php
session_start();
include("../../Koneksi/koneksi.php"); // Path 2x ../

if (!isset($_SESSION['verification_email'])) {
    header('Location: login.php');
    exit;
}
$email = $_SESSION['verification_email'];

$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda - Ukopia</title>

    <link rel="stylesheet" href="../assets/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/toast.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
        }
    </style>
</head>

<body>

    <div class="verify-wrapper">

        <div class="verification-status">

            <div class="verification-icon" style="background: #f59e0b;">
                <i class="fas fa-envelope"></i>
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

    <script src="../assets/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const resendButton = document.getElementById('resend-button');
            const countdownTimer = document.getElementById('countdown-timer');
            let cooldown = 60; // 60 detik

            function startCooldown() {
                resendButton.classList.add('disabled');
                resendButton.style.pointerEvents = 'none'; // Matikan link

                countdownTimer.textContent = `Bisa kirim ulang dalam ${cooldown} detik...`;

                const interval = setInterval(() => {
                    cooldown--;
                    countdownTimer.textContent = `Bisa kirim ulang dalam ${cooldown} detik...`;

                    if (cooldown <= 0) {
                        clearInterval(interval);
                        countdownTimer.textContent = '';
                        resendButton.classList.remove('disabled');
                        resendButton.style.pointerEvents = 'auto'; // Hidupkan link
                        localStorage.removeItem('cooldown_start'); // Hapus timestamp
                    }
                }, 1000); // 1 detik
            }

            const status = "<?= htmlspecialchars($status) ?>";
            const message = "<?= htmlspecialchars($message) ?>";

            if (status === 'success') {
                showToast(message, 'success');
                localStorage.setItem('cooldown_start', Date.now());
                startCooldown();
            } else if (status === 'error') {
                showToast(message, 'error');
            }

            resendButton.addEventListener('click', function(e) {
                if (resendButton.classList.contains('disabled')) {
                    e.preventDefault(); // Hentikan klik jika masih cooldown
                    showToast('Harap tunggu 60 detik sebelum mengirim ulang.', 'error'); // Ganti warning jadi error/info style
                }
            });

            const cooldownStart = localStorage.getItem('cooldown_start');
            if (cooldownStart) {
                const timePassed = Math.floor((Date.now() - cooldownStart) / 1000);
                if (timePassed < 60) {
                    cooldown = 60 - timePassed;
                    startCooldown();
                } else {
                    localStorage.removeItem('cooldown_start');
                }
            }
        });
    </script>

</body>

</html>
