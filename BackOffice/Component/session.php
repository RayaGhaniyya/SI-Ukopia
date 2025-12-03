<?php
session_start();
$session_lifetime = 24 * 60 * 60; 


if (!isset($_SESSION['username'])) {
    header("Location: /SI-Ukopia/BackOffice/Auth/indexlogin.php");
    exit();
}


if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}


if (time() - $_SESSION['login_time'] > $session_lifetime) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['notif'] = "Sesi kamu telah berakhir. Silakan login kembali.";
    $_SESSION['type'] = "error";
    header("Location: /SI-Ukopia/BackOffice/Auth/indexlogin.php");
    exit();
}


if (isset($_SESSION['notif']) && !empty($_SESSION['notif'])) {
    $notif = $_SESSION['notif'];
    $type = $_SESSION['type'] ?? 'info'; 

    unset($_SESSION['notif']);
    unset($_SESSION['type']);
} else {
    $notif = '';
    $type = '';
}
