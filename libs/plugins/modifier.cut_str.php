<?php

/**
 * Cut string as UTF8
 *
 * @param string $string string
 * @param integer $length max words to reveal
 * @param string $symbol content replacement
 */
function smarty_modifier_cut_str($string, $length = 30, $symbol = '...') {
    global $chan;

	return $chan->cutStr($string, $length, $symbol);
}
