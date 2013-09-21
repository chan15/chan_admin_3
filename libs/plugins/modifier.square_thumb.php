<?php
/**
 * square thumb
 */
function smarty_modifier_square_thumb($src, $path = '/', $ratio = 100, $noFile = '檔案不存在') {
	include_once dirname(dirname(dirname(__FILE__))).'/libs/class.chan.php';
	$chan = new chan;
	return $chan->squareThumb($path, $src, $ratio, $noFile);
}
?>
