<?php
define('TARGET_WIDTH', 800);
define('WEBP_QUALITY', 85);

/**
 * Mengoptimalkan, mengubah ukuran, dan menyimpan gambar sebagai WebP.
 *
 * @param array $file Data file dari $_FILES['nama_input']
 * @param string $uploadDir Direktori tujuan (e.g., '../Uploads/Menu/')
 * @return string|false Nama file baru jika berhasil, false jika gagal.
 */
function optimizeAndSaveImage($file, $uploadDir) {
    $tempName = $file['tmp_name'];
    $fileType = $file['type'];

    $newFileName = 'menu_' . time() . '_' . rand(100, 999) . '.webp';
    $destination = $uploadDir . $newFileName;

    list($originalWidth, $originalHeight) = getimagesize($tempName);

    $ratio = $originalWidth / $originalHeight;
    if ($originalWidth > TARGET_WIDTH) {
        $targetHeight = TARGET_WIDTH / $ratio;
        $targetWidth = TARGET_WIDTH;
    } else {
        $targetWidth = $originalWidth;
        $targetHeight = $originalHeight;
    }

    $sourceImage = null;
    switch ($fileType) {
        case 'image/jpeg':
        case 'image/jpg':
            $sourceImage = imagecreatefromjpeg($tempName);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($tempName);
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

    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

    if ($fileType == 'image/png' || $fileType == 'image/webp') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefilledrectangle($resizedImage, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    imagecopyresampled(
        $resizedImage, $sourceImage,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $originalWidth, $originalHeight
    );

    if (imagewebp($resizedImage, $destination, WEBP_QUALITY)) {
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        return $newFileName; // Kembalikan nama file baru
    }

    imagedestroy($sourceImage);
    imagedestroy($resizedImage);
    return false; // Gagal menyimpan
}
?>
