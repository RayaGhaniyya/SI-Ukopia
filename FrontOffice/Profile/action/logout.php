<?php
session_start();
session_unset();
session_destroy();
header('Location: ../../auth/login.php?status=success&message=Anda berhasil logout.');
exit;
