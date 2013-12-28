<?php
/**
 * Make square thumbnail
 *
 * @param string $src file source
 * @param string $path file path
 * @param integer $ratio thumbnail ratio
 * @param string $noFile message when file not exist
 * @return string
 */
function smarty_modifier_square_thumb($src, $path = '/', $ratio = 100, $noFile = '', $nameOnly = false) {
    global $chan;
	return $chan->squareThumb($path, $src, $ratio, $noFile, $nameOnly);
}
?>
