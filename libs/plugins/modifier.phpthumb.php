<?php
/**
 * Thumbnail by phpthumb library
 *
 * @param string $file file name
 * @param string $path file path
 * @param integer $width width
 * @param integer $height height
 * @param string $method (thumb|fit|square)
 * @return thumbnail
 */
function smarty_modifier_phpthumb($file, $path,  $width = 0, $height = 0, $method = 'thumb') {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $quality = 100;
    $thumbPath = dirname($path) . '/thumb/';
    $path = '../' . str_replace('../', '', $path);

    if ('jpg' === $extension) {
        $extension = 'jpeg';
    }

    switch ($method) {
        case 'thumb':
            return sprintf('%sphpThumb.php?src=%s&f=%s&q=%s&w=%s&h=%s',
                $thumbPath ,
                $path . $file,
                $extension,
                $quality,
                $width,
                $height);
            break;
        case 'fit':
            if (0 === $width || 0 === $height) {
                return sprintf('%sphpThumb.php?src=%s&f=%s&q=%s&far=T&aoe=1&w=%s&h=%s',
                    $thumbPath,
                    $path . $file,
                    $extension,
                    $quality,
                    $width,
                    $height);
            } else {
                return sprintf('%sphpThumb.php?src=%s&f=%s&q=%s&zc=T&aoe=1&w=%s&h=%s',
                    $thumbPath,
                    $path . $file,
                    $extension,
                    $quality,
                    $width,
                    $height);
            }
            break;
        case 'square':
            return sprintf('%sphpThumb.php?src=%s&f=%s&q=%s&zc=T&w=%s&h=%s',
                $thumbPath,
                $path . $file,
                $extension,
                $quality,
                $width,
                $height);
            break;
    }
}
