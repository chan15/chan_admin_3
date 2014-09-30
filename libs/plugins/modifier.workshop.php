<?php

/**
 * Make thumbnail by Workshop
 *
 * @param string $source image
 * @param string $path image path
 * @param string $ratio image ratio
 * @param string $method thumbnail method ('resize'|'square'|'fit')
 * @return image
 */
function smarty_modifier_workshop($source, $path, $ratio, $method = 'resize') {
    $file = $path . $source;

    return SITE_HOST . "/workshop.php?path={$file}&ratio={$ratio}&method={$method}";
}
