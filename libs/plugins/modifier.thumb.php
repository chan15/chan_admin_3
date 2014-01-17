<?php
/**
 * Make thumbnail
 *
 * @param string $src file source
 * @param string $path file path
 * @param integer $width thumbnail width
 * @param integer $height thumbnail height
 * @param string $noFile message when file not exist
 * @param string $nameOnly return image name only if true
 * @return string
 */
function smarty_modifier_thumb($src, $path = '/', $width = 100, $height = 100, $noFile = '', $nameOnly = false) {
    global $chan;
	return $chan->thumb($path, $src, $width, $height, $noFile, $nameOnly);
}
?>
