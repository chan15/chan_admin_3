<?php
/**
 * UTF8 截字
 */
function smarty_modifier_cut_str($string, $length = 30, $symbol = '...') { 
	include_once dirname(dirname(dirname(__FILE__))).'/libs/class.chan.php';
	$chan = new chan;
	return $chan->cutStr($string, $length, $symbol);
} 
?>
