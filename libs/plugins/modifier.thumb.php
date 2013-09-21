<?php
/**
 * 縮圖功能
 */
function smarty_modifier_thumb($src, $path = '/', $width = 100, $height = 100, $noFile = '檔案不存在') {
	include_once dirname(dirname(dirname(__FILE__))).'/libs/class.chan.php';
	$chan = new chan;
	return $chan->thumb($path, $src, $width, $height, $noFile);
}
?>
