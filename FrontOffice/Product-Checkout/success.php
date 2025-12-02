<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pesanan Berhasil</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            padding: 50px;
            background: #f8f8f8;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        h1 {
            color: #28a745;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>✅ Pesanan Diterima!</h1>
        <p>Terima kasih. Pesanan Anda sedang kami proses.</p>
        <p>ID Order: <strong><?= htmlspecialchars($_GET['order_id'] ?? '-') ?></strong></p>
        <a href="../HomePage/index.php">Kembali ke Beranda</a>
    </div>
</body>
</html>
