<?php
include '../../Koneksi/koneksi.php';
session_start();
$session_lifetime = 24 * 60 * 60;
if (isset($_SESSION['username']) && isset($_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] < $session_lifetime) {
        header("Location: ../Dashboard/index.php");
        exit();
    } else {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['notif'] = "Sesi kamu telah berakhir. Silakan login kembali.";
        $_SESSION['type'] = "error";
    }
}
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    if (empty($username) || empty($password)) {
        $_SESSION['notif'] = "Username dan password wajib diisi!";
        $_SESSION['type'] = "error";
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['login_time'] = time();
                $_SESSION['notif'] = "Login berhasil! Selamat datang";
                $_SESSION['type'] = "success";
                header("Location: ../Dashboard/index.php");
                exit();
            } else {
                $_SESSION['notif'] = "Password salah!";
                $_SESSION['type'] = "error";
            }
        } else {
            $_SESSION['notif'] = "Username tidak ditemukan!";
            $_SESSION['type'] = "error";
        }
        $stmt->close();
    }
    header("Location: indexlogin.php");
    exit();
}
$notif = $_SESSION['notif'] ?? '';
$type = $_SESSION['type'] ?? '';
unset($_SESSION['notif']);
unset($_SESSION['type']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ukopia</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="title">Login</h2>
            <form method="POST" class="login-form">
                <div class="input-box">
                    <span class="icon material-icons">person</span>
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="password" required>
                    <label>Password</label>
                    <span class="toggle-password material-icons" onclick="togglePassword('password', this)">visibility_off</span>
                </div>
                <button type="submit" name="login" class="btn">Masuk</button>
                <p class="register-link">
                    Belum punya akun? <a href="indexregister.php">Daftar di sini</a>
                </p>
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
</body>
</html>

