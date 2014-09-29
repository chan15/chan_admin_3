<?php
/**
 * UTF8 截字
 */
function smarty_modifier_cut_str($string, $length = 30, $symbol = '...') {
    global $chan;

	return $chan->cutStr($string, $length, $symbol);
}
?>
