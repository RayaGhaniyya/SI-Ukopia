<?php
session_start();
session_unset();
session_destroy();
session_start();
$_SESSION['notif'] = "Kamu berhasil logout!";
$_SESSION['type'] = "success";
header("Location: indexlogin.php");
exit();

