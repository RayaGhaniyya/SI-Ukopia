<?php
// Tentukan lebar maksimum untuk resize (opsional)
define('TARGET_WIDTH', 800);
// Level Kompresi PNG (0 = tidak dikompres, 9 = kompresi maksimal). 
// Standar yang baik adalah 6.
define('PNG_COMPRESSION', 6);

function optimizeAndSaveImage($file, $uploadDir) {
    $tempName = $file['tmp_name'];
    $fileType = $file['type'];
    
    // [UBAH] Nama file unik dengan ekstensi .png
    $newFileName = 'alat_' . time() . '_' . rand(100, 999) . '.png';
    $destination = $uploadDir . $newFileName;

    // Dapatkan ukuran asli
    list($originalWidth, $originalHeight) = getimagesize($tempName);
    
    // Hitung rasio resize
    $ratio = $originalWidth / $originalHeight;
    
    if ($originalWidth > TARGET_WIDTH) {
        $targetHeight = TARGET_WIDTH / $ratio;
        $targetWidth = TARGET_WIDTH;
    } else {
        $targetWidth = $originalWidth;
        $targetHeight = $originalHeight;
    }

    // Load gambar sumber
    $sourceImage = null;
    switch ($fileType) {
        case 'image/jpeg': 
        case 'image/jpg': 
            $sourceImage = imagecreatefromjpeg($tempName); 
            break;
        case 'image/png': 
            $sourceImage = imagecreatefrompng($tempName); 
            break;
        case 'image/webp': 
            $sourceImage = imagecreatefromwebp($tempName); 
            break;
        default: 
            return false; // Format tidak didukung
    }

    if ($sourceImage === null) return false;

    // Buat canvas baru
    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // [PENTING] Pertahankan Transparansi untuk PNG
    imagealphablending($resizedImage, false);
    imagesavealpha($resizedImage, true);
    
    // Jika sumbernya transparan (PNG/WebP), kita buat background transparan dulu
    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
    imagefilledrectangle($resizedImage, 0, 0, $targetWidth, $targetHeight, $transparent);

    // Resize gambar
    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);

    // [UBAH] Simpan sebagai PNG
    if (imagepng($resizedImage, $destination, PNG_COMPRESSION)) {
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        return $newFileName;
    }

    // Bersihkan memori jika gagal
    if ($sourceImage) imagedestroy($sourceImage);
    if ($resizedImage) imagedestroy($resizedImage);
    
    return false;
}
?>