<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");

// 1. Ambil Filter
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'SEMUA';

// 2. Filter Status (Hanya ambil yang valid/uang masuk)
if ($status_filter !== 'SEMUA') {
    $status_sql = "AND t.status_pesanan = '$status_filter'";
} else {
    // Abaikan Batal, Kadaluarsa, Pengajuan Batal, Menunggu Pembayaran
    $status_sql = "AND t.status_pesanan IN ('Selesai', 'Dikirim', 'Diproses', 'Sudah Dibayar')";
}

// 3. Query Kompleks (Join Produk, Size, Grind)
// GROUP_CONCAT dipakai biar 1 Transaksi cuma muncul 1 Baris, tapi itemnya numpuk di kolom "Detail Item"
$query = "
    SELECT 
        t.id_transaksi,
        t.midtrans_order_id,
        t.tanggal_pesan,
        t.total_harga_barang,
        t.ongkir,
        t.total_pembayaran,
        c.nama as nama_customer,
        GROUP_CONCAT(
            CONCAT(
                '- ', p.nama_produk, 
                ' <b>(', IFNULL(sz.ukuran, '-'), 
                IF(gs.nama_grind IS NOT NULL, CONCAT(', ', gs.nama_grind), ''), ')</b>',
                ' x', dt.jumlah
            ) 
            SEPARATOR '<br>'
        ) as list_item
    FROM transaksi t
    JOIN akun_customer c ON t.uid_customer = c.uid
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
    JOIN produk p ON dp.id_produk = p.id_produk
    LEFT JOIN size sz ON dp.id_size = sz.id_size
    LEFT JOIN grind_size gs ON dp.id_grind = gs.id_grind
    WHERE DATE(t.tanggal_pesan) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    $status_sql
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal_pesan DESC
";

$result = mysqli_query($conn, $query);
$total_omzet = 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Ukopia</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header p {
            margin: 5px 0;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background-color: #eee;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
            font-style: italic;
        }

        /* Tombol Print (Hilang pas diprint) */
        .btn-print {
            padding: 10px 20px;
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-print:hover {
            background: #000;
        }

        @media print {
            .no-print {
                display: none;
            }

            @page {
                size: A4 landscape;
                margin: 1cm;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between;">
        <button onclick="window.history.back()" style="cursor:pointer; background:none; border:1px solid #ccc; padding:5px 10px;">&larr; Kembali</button>
        <button onclick="window.print()" class="btn-print">Cetak PDF / Print</button>
    </div>

    <div class="header">
        <h1>UKOPIA</h1>
        <p>Laporan Penjualan Produk</p>
        <p>Periode: <b><?= date('d M Y', strtotime($tgl_awal)) ?></b> s/d <b><?= date('d M Y', strtotime($tgl_akhir)) ?></b></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tgl & ID</th>
                <th width="15%">Customer</th>
                <th>Detail Item (Produk, Size, Grind)</th>
                <th width="15%" class="text-right">Total Belanja (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $total_omzet += $row['total_harga_barang'];
            ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($row['tanggal_pesan'])) ?><br>
                            <small style="color:#666;"><?= $row['midtrans_order_id'] ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['nama_customer']) ?></td>
                        <td style="line-height: 1.5;"><?= $row['list_item'] ?></td>
                        <td class="text-right font-bold"><?= number_format($row['total_harga_barang'], 0, ',', '.') ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='5' class='text-center' style='padding: 20px;'>Tidak ada data transaksi sukses pada periode ini.</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #eee;">
                <td colspan="4" class="text-right font-bold" style="font-size: 14px;">TOTAL OMZET (Gross Sales)</td>
                <td class="text-right font-bold" style="font-size: 14px;">Rp <?= number_format($total_omzet, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Informasi Ukopia pada: <?= date('d F Y H:i') ?>
    </div>

</body>

</html>