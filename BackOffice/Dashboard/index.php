<?php
include("../Component/session.php");
include("../Component/head.php");
include("../Component/loader.php");
?>

<link rel="stylesheet" href="../assets/css/login.css">

<div class="container">
    <div>
        <?php
        include("../Component/sidebar.php");
        ?>
    </div>
    <div class="card" style="text-align:center;">
        <h2>Selamat Datang,
            <?php
            if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
                echo htmlspecialchars($_SESSION['username']);
            } else {
                echo 'Guest';
            }
            ?>
        </h2>
        <p>Kamu berhasil login ke Dashboard.</p>

        <form action="../Auth/indexlogout.php" method="POST">
            <button type="submit" class="btn" style="margin-top:20px;">Logout</button>
        </form>
    </div>
</div>

<script src="../assets/js/login.js"></script>
<?php if (!empty($notif)): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            showNotification("<?= htmlspecialchars($notif) ?>", "<?= $type ?>");
        });
    </script>
<?php endif; ?>


<?php
include("../Component/bottom.php");
?>