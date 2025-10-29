<?php
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

// Validasi input
$id_galery = isset($_POST['id_galery']) ? intval($_POST['id_galery']) : 0;
$judul = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';

if ($id_galery <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID galeri tidak valid!']);
    exit;
}

if (empty($judul) || empty($deskripsi) || empty($tanggal)) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']);
    exit;
}

// Validasi tanggal format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid!']);
    exit;
}

// Cek apakah galeri exists
$stmt_check = $conn->prepare("SELECT id_galery FROM galery WHERE id_galery = ?");
$stmt_check->bind_param("i", $id_galery);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Data galeri tidak ditemukan!']);
    exit;
}
$stmt_check->close();

// Mulai transaksi
$conn->begin_transaction();

try {
    // Update data galeri
    $stmt = $conn->prepare("UPDATE galery SET judul = ?, deskripsi = ?, tanggal = ? WHERE id_galery = ?");
    $stmt->bind_param("sssi", $judul, $deskripsi, $tanggal, $id_galery);

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data galeri");
    }
    $stmt->close();

    // Cek apakah ada file gambar baru yang diupload
    $hasNewImages = isset($_FILES['gambar']) && !empty($_FILES['gambar']['tmp_name'][0]);

    if ($hasNewImages) {
        // Validasi jumlah file (max 4)
        $totalFiles = count($_FILES['gambar']['tmp_name']);
        if ($totalFiles > 4) {
            throw new Exception("Maksimal 4 gambar!");
        }

        // Validasi file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $uploadedFiles = [];

        for ($i = 0; $i < $totalFiles; $i++) {
            if (empty($_FILES['gambar']['tmp_name'][$i])) continue;

            $fileName = $_FILES['gambar']['name'][$i];
            $fileTmp = $_FILES['gambar']['tmp_name'][$i];
            $fileSize = $_FILES['gambar']['size'][$i];
            $fileType = $_FILES['gambar']['type'][$i];
            $fileError = $_FILES['gambar']['error'][$i];

            if ($fileError !== UPLOAD_ERR_OK) {
                throw new Exception("Error upload file: {$fileName}");
            }

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("File {$fileName} bukan gambar yang valid!");
            }

            if ($fileSize > $maxSize) {
                throw new Exception("File {$fileName} terlalu besar! Max 5MB");
            }

            $uploadedFiles[] = [
                'tmp' => $fileTmp,
                'name' => $fileName,
                'type' => $fileType
            ];
        }

        // Ambil gambar lama untuk dihapus
        $stmt_old = $conn->prepare("SELECT gambar FROM detail_galery WHERE id_galery = ?");
        $stmt_old->bind_param("i", $id_galery);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $oldImages = [];
        while ($row = $result_old->fetch_assoc()) {
            $oldImages[] = $row['gambar'];
        }
        $stmt_old->close();

        // Hapus record gambar lama dari database
        $stmt_delete = $conn->prepare("DELETE FROM detail_galery WHERE id_galery = ?");
        $stmt_delete->bind_param("i", $id_galery);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Upload gambar baru
        $uploadDir = "../../assets/img/gallery/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $stmt_detail = $conn->prepare("INSERT INTO detail_galery (id_galery, gambar) VALUES (?, ?)");

        foreach ($uploadedFiles as $index => $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = "gallery_" . $id_galery . "_" . time() . "_" . ($index + 1) . "." . $extension;
            $targetPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($file['tmp'], $targetPath)) {
                throw new Exception("Gagal upload file: " . $file['name']);
            }

            $relativePath = "assets/img/gallery/" . $newFileName;
            $stmt_detail->bind_param("is", $id_galery, $relativePath);

            if (!$stmt_detail->execute()) {
                unlink($targetPath);
                throw new Exception("Gagal menyimpan detail gambar");
            }
        }

        $stmt_detail->close();

        // Hapus file gambar lama dari server (setelah berhasil upload baru)
        foreach ($oldImages as $oldPath) {
            $fullPath = "../../" . $oldPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    // Commit transaksi
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Galeri berhasil diperbarui!'
    ]);
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
