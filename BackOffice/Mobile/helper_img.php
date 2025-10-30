<?php
// Tentukan lebar maksimum untuk gambar yang dioptimalkan
define('TARGET_WIDTH', 800);
// Tentukan kualitas output untuk WebP (0-100)
define('WEBP_QUALITY', 85);

/**
 * Mengoptimalkan, mengubah ukuran, dan menyimpan gambar sebagai WebP.
 *
 * @param array $file Data file dari $_FILES['nama_input']
 * @param string $uploadDir Direktori tujuan (e.g., '../../../Uploads/Menu/')
 * @return string|false Nama file baru jika berhasil, false jika gagal.
 */
function optimizeAndSaveImage($file, $uploadDir) {
    $tempName = $file['tmp_name'];
    $fileType = $file['type'];

    // Buat nama file unik baru dengan ekstensi .webp
    $newFileName = 'menu_' . time() . '_' . rand(100, 999) . '.webp';
    $destination = $uploadDir . $newFileName;

    // Dapatkan ukuran asli gambar
    list($originalWidth, $originalHeight) = getimagesize($tempName);

    // Hitung tinggi baru berdasarkan rasio aspek
    $ratio = $originalWidth / $originalHeight;
    if ($originalWidth > TARGET_WIDTH) {
        $targetHeight = TARGET_WIDTH / $ratio;
        $targetWidth = TARGET_WIDTH;
    } else {
        $targetWidth = $originalWidth;
        $targetHeight = $originalHeight;
    }

    // Buat gambar sumber berdasarkan tipe file
    $sourceImage = null;
    switch ($fileType) {
        case 'image/jpeg':
        case 'image/jpg':
            $sourceImage = imagecreatefromjpeg($tempName);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($tempName);
            // Menjaga transparansi untuk PNG
            imagepalettetotruecolor($sourceImage);
            imagealphablending($sourceImage, true);
            imagesavealpha($sourceImage, true);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($tempName);
            break;
        default:
            return false; // Tipe file tidak didukung
    }

    if ($sourceImage === null) {
        return false;
    }

    // Buat canvas baru untuk gambar yang di-resize
    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

    // Jika PNG, jaga transparansi saat resize
    if ($fileType == 'image/png' || $fileType == 'image/webp') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefilledrectangle($resizedImage, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    // Resize gambar
    imagecopyresampled(
        $resizedImage, $sourceImage,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $originalWidth, $originalHeight
    );

    // Simpan gambar baru sebagai WebP
    if (imagewebp($resizedImage, $destination, WEBP_QUALITY)) {
        // Bebaskan memori
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        return $newFileName; // Kembalikan nama file baru
    }

    imagedestroy($sourceImage);
    imagedestroy($resizedImage);
    return false; // Gagal menyimpan
}
?>