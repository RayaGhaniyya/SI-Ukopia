<?php

define('TARGET_WIDTH', 800);


define('PNG_COMPRESSION', 6);

function optimizeAndSaveImage($file, $uploadDir) {
    $tempName = $file['tmp_name'];
    $fileType = $file['type'];
    
    
    $newFileName = 'alat_' . time() . '_' . rand(100, 999) . '.png';
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
            break;
        case 'image/webp': 
            $sourceImage = imagecreatefromwebp($tempName); 
            break;
        default: 
            return false; 
    }

    if ($sourceImage === null) return false;

    
    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
    
    
    imagealphablending($resizedImage, false);
    imagesavealpha($resizedImage, true);
    
    
    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
    imagefilledrectangle($resizedImage, 0, 0, $targetWidth, $targetHeight, $transparent);

    
    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);

    
    if (imagepng($resizedImage, $destination, PNG_COMPRESSION)) {
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        return $newFileName;
    }

    
    if ($sourceImage) imagedestroy($sourceImage);
    if ($resizedImage) imagedestroy($resizedImage);
    
    return false;
}
?>