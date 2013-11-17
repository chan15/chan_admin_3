<?php
/**
 * Make fit thumbnail
 *
 * @param string $src file source
 * @param string $path file path
 * @param integer $width thumbnail width
 * @param integer $height thumbnail height
 * @param string $noFile message when file not exist
 * @param string $nameOnly return image name only if true
 * @return string
 */
function smarty_modifier_fit_thumb($src, $path = '/', $width = 100, $height = 100, $noFile = '', $nameOnly = false) {
	include_once dirname(dirname(dirname(__FILE__))) . '/libs/class.chan.php';
	$chan = new chan;
	return $chan->fitThumb($path, $src, $width, $height, $noFile, $nameOnly);
}
?>
