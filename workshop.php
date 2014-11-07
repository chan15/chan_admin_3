<?php

include 'main.php';

$path = $_GET['path'];

if (false === file_exists($path)) {
    $path = 'assets/default/images/transparent.png';
}

$ratio = $_GET['ratio'];
$denomination = explode('x', $ratio);
$method = $_GET['method'];

if (count($denomination) > 1) {
    $width = $denomination[0];
    $height = $denomination[1];
}

$ext = pathinfo($path, PATHINFO_EXTENSION);
$file = pathinfo($path, PATHINFO_FILENAME) . '.' . $ext;

$layer = PHPImageWorkshop\ImageWorkshop::initFromPath($path);
$sourceWidth = $layer->getWidth();
$sourceHeight = $layer->getHeight();

if ($sourceWidth > $sourceHeight) {
    $padding = (($sourceWidth - $sourceHeight) * 2);
} else {
    $padding = (($sourceHeight - $sourceWidth) * 2);
}

switch ($method) {
    case 'resize':
        $layer->resizeByLargestSideInPixel($width, $height);
        break;
    case 'square':
        if ($ratio > $sourceWidth || $ratio > $sourceHeight) {
            if ($width >= $height) {
                // Landscape
                $layer->resizeInPixel($ratio + $sourceWidth + $padding, null, true, 0, 0, 'MM');
            } else {
                // Portrait
                $layer->resizeInPixel(null, $ratio + $sourceHeight + $padding, true, 0, 0, 'MM');
            }
        }

        $layer->cropInPixel($ratio, $ratio, 0, 0, 'MM');
        break;
    case 'fit':
        if ($width > $sourceWidth || $height > $sourceHeight) {
            if ($width >= $height) {
                // Landscape
                $layer->resizeInPixel($width, null, true, 0, 0, 'MM');
            } else {
                // Portrait
                $layer->resizeInPixel(null, $height, true, 0, 0, 'MM');
            }
        } else {
            $layer->resizeByLargestSideInPixel($width, $height);
        }

        $layer->cropInPixel($width, $height, 0, 0, 'MM');
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
