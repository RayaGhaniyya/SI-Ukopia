<?php
define('TARGET_WIDTH', 800);
define('WEBP_QUALITY', 85);

function optimizeAndSaveImage($file, $uploadDir) {
    $tempName = $file['tmp_name'];
    $fileType = $file['type'];
    
    // Nama file unik
    $newFileName = 'alat_' . time() . '_' . rand(100, 999) . '.webp';
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
        case 'image/jpeg': case 'image/jpg': $sourceImage = imagecreatefromjpeg($tempName); break;
        case 'image/png': 
            $sourceImage = imagecreatefrompng($tempName); 
            imagepalettetotruecolor($sourceImage);
            imagealphablending($sourceImage, true);
            imagesavealpha($sourceImage, true);
            break;
        case 'image/webp': $sourceImage = imagecreatefromwebp($tempName); break;
        default: return false;
    }

    if ($sourceImage === null) return false;

    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
    
    if ($fileType == 'image/png' || $fileType == 'image/webp') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefilledrectangle($resizedImage, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);

    if (imagewebp($resizedImage, $destination, WEBP_QUALITY)) {
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        return $newFileName;
    }

    return false;
}
?>