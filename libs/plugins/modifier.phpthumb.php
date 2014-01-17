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

    if ('jpg' === $extension) {
        $extension = 'jpeg';
    }

    switch ($method) {
        case 'thumb':
            return sprintf('thumb/phpThumb.php/f=%s;q=%s;%sx%s;%s',
                $extension,
                $quality,
                $width,
                $height,
                $path . $file);
            break;
        case 'fit':
            if (0 === $width || 0 === $height) {
                return sprintf('thumb/phpThumb.php/f=%s;q=%s;far=T;aoe=1;%sx%s;%s',
                    $extension,
                    $quality,
                    $width,
                    $height,
                    $path . $file);
            } else {
                return sprintf('thumb/phpThumb.php/f=%s;q=%s;zc=T;aoe=1;%sx%s;%s',
                    $extension,
                    $quality,
                    $width,
                    $height,
                    $path . $file);
            }
            break;
        case 'square':
            return sprintf('thumb/phpThumb.php/f=%s;q=%s;zc=T;%sx%s;%s',
                $extension,
                $quality,
                $width,
                $height,
                $path . $file);
            break;
    }
}
