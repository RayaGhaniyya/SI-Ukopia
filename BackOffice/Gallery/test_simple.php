<?php
include("../../Koneksi/koneksi.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #3b82f6;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            margin: 2px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-info {
            background: #06b6d4;
        }

        .btn-warning {
            background: #f59e0b;
        }

        .btn-danger {
            background: #ef4444;
        }

        h1 {
            color: #333;
        }
    </style>
</head>

<body>
    <h1>🔍 TEST GALLERY - Simple Version</h1>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Tanggal</th>
                <th>Total Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "
                SELECT 
                    g.id_galery, 
                    g.judul, 
                    g.deskripsi, 
                    g.tanggal,
                    COUNT(d.id_detail_galery) as total_foto
                FROM galery g
                LEFT JOIN detail_galery d ON g.id_galery = d.id_galery
                GROUP BY g.id_galery, g.judul, g.deskripsi, g.tanggal
                ORDER BY g.id_galery DESC
            ";

            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                    <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo $row['id_galery']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td><?php echo $row['total_foto']; ?> foto</td>
                        <td>
                            <button class="btn btn-info"><i class="fas fa-eye"></i></button>
                            <a href="update.php?id=<?php echo $row['id_galery']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php
                    $no++;
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        ❌ TIDAK ADA DATA atau ERROR QUERY!
                        <br><br>
                        Error: <?php echo mysqli_error($conn); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    <br>
    <a href="index.php" style="padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;">
        ← Kembali ke Index.php
    </a>
</body>

</html>
```

---

## 🎯 **Akses File Ini:**
```
http://localhost/SI-ukopia/BackOffice/Gallery/test_simple.php