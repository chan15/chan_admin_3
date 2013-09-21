<?php
/**
 * 縮圖文字功能
 */
function smarty_modifier_thumb_str($str, $path = '/', $width = 100, $height = 100) {
	include_once dirname(dirname(dirname(__FILE__))).'/libs/class.chan.php';
	$chan = new chan;
	$chan->thumb($path, $str, $width, $height, '');

	$strArr = explode('.', $str);
	return $path.'thumbnails/'.$strArr[0].'_'.$width.'x'.$height.'.'.$strArr[1];
}
?>
