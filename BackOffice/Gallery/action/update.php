<?php
include("../../../Koneksi/koneksi.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Method tidak valid']));
}

$id_galery = intval($_POST['id_galery'] ?? 0);
$judul = trim($_POST['judul'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');

// Validasi
if ($id_galery <= 0) exit(json_encode(['success' => false, 'message' => 'ID tidak valid!']));
if (empty($judul) || empty($deskripsi) || empty($tanggal)) {
    exit(json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    exit(json_encode(['success' => false, 'message' => 'Format tanggal tidak valid!']));
}

// Cek exists
$stmt_check = $conn->prepare("SELECT id_galery FROM galery WHERE id_galery = ?");
$stmt_check->bind_param("i", $id_galery);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    exit(json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']));
}
$stmt_check->close();

$conn->begin_transaction();

try {
    // Update gallery
    $stmt = $conn->prepare("UPDATE galery SET judul=?, deskripsi=?, tanggal=? WHERE id_galery=?");
    $stmt->bind_param("sssi", $judul, $deskripsi, $tanggal, $id_galery);
    if (!$stmt->execute()) throw new Exception("Gagal update data");
    $stmt->close();

    // Cek gambar baru
    $hasNewImages = isset($_FILES['gambar']) && !empty($_FILES['gambar']['tmp_name'][0]);

    if ($hasNewImages) {
        $totalFiles = count($_FILES['gambar']['tmp_name']);
        if ($totalFiles > 4) throw new Exception("Maksimal 4 gambar!");

        // Validasi files
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;
        $uploadedFiles = [];

        for ($i = 0; $i < $totalFiles; $i++) {
            if (empty($_FILES['gambar']['tmp_name'][$i])) continue;

            $fileError = $_FILES['gambar']['error'][$i];
            $fileType = $_FILES['gambar']['type'][$i];
            $fileSize = $_FILES['gambar']['size'][$i];
            $fileName = $_FILES['gambar']['name'][$i];

            if ($fileError !== UPLOAD_ERR_OK) throw new Exception("Error upload: {$fileName}");
            if (!in_array($fileType, $allowedTypes)) throw new Exception("File {$fileName} tidak valid!");
            if ($fileSize > $maxSize) throw new Exception("File {$fileName} terlalu besar!");

            $uploadedFiles[] = ['tmp' => $_FILES['gambar']['tmp_name'][$i], 'name' => $fileName];
        }

        // Get old images
        $stmt_old = $conn->prepare("SELECT gambar FROM detail_galery WHERE id_galery = ?");
        $stmt_old->bind_param("i", $id_galery);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $oldImages = [];
        while ($row = $result_old->fetch_assoc()) $oldImages[] = $row['gambar'];
        $stmt_old->close();

        // Delete old records
        $stmt_delete = $conn->prepare("DELETE FROM detail_galery WHERE id_galery = ?");
        $stmt_delete->bind_param("i", $id_galery);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Upload new images
        $uploadDir = "../../assets/img/gallery/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $stmt_detail = $conn->prepare("INSERT INTO detail_galery (id_galery, gambar) VALUES (?, ?)");

        foreach ($uploadedFiles as $index => $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = "gallery_{$id_galery}_" . time() . "_" . ($index + 1) . ".{$extension}";
            $targetPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($file['tmp'], $targetPath)) {
                throw new Exception("Gagal upload: " . $file['name']);
            }

            $relativePath = "assets/img/gallery/" . $newFileName;
            $stmt_detail->bind_param("is", $id_galery, $relativePath);

            if (!$stmt_detail->execute()) {
                unlink($targetPath);
                throw new Exception("Gagal menyimpan detail");
            }
        }

        $stmt_detail->close();

        // Delete old files
        foreach ($oldImages as $oldPath) {
            $fullPath = "../../" . $oldPath;
            if (file_exists($fullPath)) unlink($fullPath);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Galeri berhasil diperbarui!']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
