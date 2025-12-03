<?php
include '../../Koneksi/koneksi.php';
session_start();

if (!isset($_SESSION['notif'])) {
    $_SESSION['notif'] = "";
    $_SESSION['type'] = "";
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($nama) || empty($email) || empty($password)) {
        $_SESSION['notif'] = "Semua kolom wajib diisi!";
        $_SESSION['type'] = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['notif'] = "Format email tidak valid!";
        $_SESSION['type'] = "error";
    } elseif (strlen($password) < 8) {
        $_SESSION['notif'] = "Password minimal 8 karakter!";
        $_SESSION['type'] = "error";
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['notif'] = "Email sudah digunakan, silakan gunakan email lain!";
            $_SESSION['type'] = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (username, nama, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $nama, $email, $hashedPassword);

            if ($stmt->execute()) {
                $_SESSION['notif'] = "Registrasi berhasil! Silakan login.";
                $_SESSION['type'] = "success";
                header("Location: indexlogin.php");
                exit();
            } else {
                $_SESSION['notif'] = "Terjadi kesalahan saat menyimpan data.";
                $_SESSION['type'] = "error";
            }
        }
        $stmt->close();
    }

    header("Location: indexregister.php");
    exit();
}

$notif = $_SESSION['notif'];
$type = $_SESSION['type'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Ukopia</title>
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="../assets/css/responsive/misc-responsive.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="card">
            <h2 class="title">Daftar Akun</h2>
            <form method="POST" class="login-form" id="registerForm">

                <div class="input-box">
                    <span class="icon material-icons">person</span>
                    <input type="text" name="username" id="username" placeholder=" " required>
                    <label>Username</label>
                </div>

                <div class="input-box">
                    <span class="icon material-icons">badge</span>
                    <input type="text" name="nama" id="nama" placeholder=" " required>
                    <label>Nama Lengkap</label>
                </div>

                <div class="input-box">
                    <span class="icon material-icons">email</span>
                    <input type="email" name="email" id="email" placeholder=" " required>
                    <label>Email</label>
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder=" " required minlength="8">
                    <label>Password</label>
                    <span class="toggle-password material-icons" onclick="togglePassword('password', this)">visibility_off</span>
                </div>

                <button type="submit" name="register" class="btn">Daftar</button>

                <p class="register-link">
                    Sudah punya akun? <a href="indexlogin.php">Login di sini</a>
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

    <?php
    $_SESSION['notif'] = "";
    $_SESSION['type'] = "";
    ?>
</body>

</html>