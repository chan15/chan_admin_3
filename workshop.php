<?php

include 'main.php';

$path = $_GET['path'];
$ratio = $_GET['ratio'];
$denomination = explode('x', $ratio);
$method = $_GET['method'];

if (count($denomination) > 1) {
    $width = $denomination[0];
    $height = $denomination[1];
}

$file = pathinfo($path, PATHINFO_FILENAME) . '.' . pathinfo($path, PATHINFO_EXTENSION);
$ext = pathinfo($path, PATHINFO_EXTENSION);
$layer = PHPImageWorkshop\ImageWorkshop::initFromPath($path);
$sourceWidth = $layer->getWidth();
$sourceHeight = $layer->getHeight();

if ($sourceWidth > $sourceHeight) {
    $padding = (($sourceWidth - $sourceHeight) * 2);
} else {
    $padding = (($sourceHeight - $sourceWidth) * 2);
}

switch ($_GET['method']) {
    case 'resize':
        $layer->resizeByLargestSideInPixel($width, $height);
        break;
    case 'square':
        if ($ratio > $sourceWidth || $ratio > $sourceHeight) {
            if ($sourceWidth > $sourceHeight) {
                $layer->resizeInPixel($ratio + $sourceWidth + $padding, null, true, 0, 0, 'MT');
            } else {
                $layer->resizeInPixel(null, $ratio + $sourceHeight + $padding, true, 0, 0, 'MT');
            }
        }

        $layer->cropInPixel($ratio, $ratio, 0, 0, 'MT');
        break;
    case 'fit':
        if ($width > $sourceWidth || $height > $sourceHeight) {
            if ($width > $height) {
                $layer->resizeInPixel($width, null, true, 0, 0, 'MT');
            } else {
                $layer->resizeInPixel(null, $height, true, 0, 0, 'MT');
            }
        }

        $layer->cropInPixel($width, $height, 0, 0, 'MT');
        break;
}

$image = $layer->getResult();

switch (strtolower($ext)) {
    case 'png':
        header('Content-type: image/png');
        header('Content-Disposition: filename="' . $file . '"');
        imagepng($image, null, 8);
        break;
    case 'jpg':
    case 'jpeg':
        header('Content-type: image/jpeg');
        header('Content-Disposition: filename="' . $file . '"');
        return imagejpeg($image, null, 100);
        break;
    case 'gif':
        header('Content-type: image/gif');
        header('Content-Disposition: filename="' . $file . '"');
        return imagegif($image);
        break;
}
